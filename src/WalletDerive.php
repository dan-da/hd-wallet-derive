<?php

namespace App;

require_once __DIR__  . '/../vendor/autoload.php';

// For HD-Wallet Key Derivation
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Key\Factory\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bitcoin\Mnemonic\MnemonicFactory;
use Exception;
use App\Utils\NetworkCoinFactory;
use App\Utils\MyLogger;
use App\Utils\CashAddress;
use CoinParams\CoinParams;

// For ethereum addresses
use kornrunner\Keccak;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PublicKeyInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Serializer\Key\PublicKeySerializer;
use BitWasp\Bitcoin\Crypto\EcAdapter\EcAdapterFactory;
use Mdanter\Ecc\Serializer\Point\UncompressedPointSerializer;
use Mdanter\Ecc\EccFactory;

// For segwit extended key prefixes (ypub and friends)
use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\GlobalPrefixConfig;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\NetworkConfig;
use BitWasp\Bitcoin\Key\Deterministic\Slip132\Slip132;
use BitWasp\Bitcoin\Key\KeyToScript\KeyToScriptHelper;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\Base58ExtendedKeySerializer;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\ExtendedKeySerializer;
use App\Utils\MultiCoinRegistry;
use BitWasp\Bitcoin\Network\Slip132\BitcoinRegistry;

// For determining key type via Base58 encode/decode
use BitWasp\Buffertools\Buffer;
use BitWasp\Buffertools\Parser;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\RawExtendedKeySerializer;
use BitWasp\Bitcoin\Base58;


/* A class that implements HD wallet key/address derivation
 */
class WalletDerive
{

    // Contains options we care about.
    protected $params;
    protected $hkf;
    
    public function __construct($params)
    {
        $this->params = $params;
        $this->hkf = new HierarchicalKeyFactory();
    }

    /* Getter for params
     */
    private function get_params()
    {
        return $this->params;
    }    
    
    /* Derives child keys/addresses for a given key.
     */
    public function derive_keys($key)
    {
        $params = $this->get_params();
        return $this->derive_keys_worker($params, $key);
    }


    private function derive_keys_worker($params, $key)
    {
        $coin = $params['coin'];
        list($symbol) = explode('-', $coin);
        $addrs = array();

        $bip44_coin = $this->getCoinBip44($coin);  // bip44/slip-0044 coin identifier
        
        $networkCoinFactory = new NetworkCoinFactory();
        $network = $networkCoinFactory->getNetworkCoinInstance($coin);
        Bitcoin::setNetwork($network);
        $key_type = $this->getKeyTypeFromCoinAndKey($coin, $key);

        $master = $this->fromExtended($coin, $key, $network, $key_type);

        $start = $params['startindex'];
        $end = $params['startindex'] + $params['numderive'];
        $numderive = $params['numderive'];

        /*
         *  ROOT PATH INCLUSION
         * */
        if( $params['includeroot'] ) {
            $this->derive_key_worker($coin, $symbol, $network, $addrs, $master, $key_type, null, 'm');
        }

        MyLogger::getInstance()->log( "Deriving keys", MyLogger::info );
        $path_base = is_numeric( $params['path'][0] ) ?  'm/' . $params['path'] : $params['path'];
        
        // Allow paths to end with i or i'.
        // i' specifies that addresses should be hardened.
        $pparts = explode('/', $path_base);
        
        $iter_part = null;
        foreach($pparts as $idx => $pp) {
            if($pp[0] == 'x') {
                $iter_part = $idx;
            }
        }
        if(!$iter_part) {
            $iter_part = count($pparts);
            $pparts[] = 'x';
        }
        $path_normal = implode('/', $pparts);
        $path_mask = str_replace('x', '%d', $path_normal);
        if(strpos($path_mask, 'c') !== false) {
            if( is_int($bip44_coin) ) {
                $path_mask = str_replace('c', $bip44_coin, $path_mask);  // auto-insert bip44 coin-type if requested via 'c'.
            }
            else {
                throw new Exception("'c' is present in path but Bip44 coin type is undefined for $coin");
            }
        }
        $path_mask = str_replace('v', @$params['path-change'], $path_mask);
        $path_mask = str_replace('a', @$params['path-account'], $path_mask);

        $count = 0;
        $period_start = time();
        for($i = $start; $i < $end; $i++)
        {
            $path = sprintf($path_mask, $i);
            $key = $master->derivePath($path);
            
            $this->derive_key_worker($coin, $symbol, $network, $addrs, $key, $key_type, $i, $path);
            
            $count = $i + 1;
            if(time() - $period_start > 10)
            {
                $pct = round($count / $numderive * 100, 2);
                MyLogger::getInstance()->log( "Derived $count of $numderive keys.  ($pct%)", MyLogger::specialinfo );
                $period_start = time();
            }
        }
        MyLogger::getInstance()->log( "Derived $count keys", MyLogger::info );

        return $addrs;
    }
    
    private function derive_key_worker($coin, $symbol, $network, &$addrs, $key, $key_type, $index, $path) {

        if( !$this->networkSupportsKeyType($network, $key_type, $coin ) ) {
            throw new Exception("$key_type extended keys are not supported for $coin" );
        }

        $params = $this->get_params();
        if(method_exists($key, 'getPublicKey')) {
            $address = strtolower($symbol) == 'eth' ?
                $address = $this->getEthereumAddress($key->getPublicKey()) :
                           $this->address($key, $network);
                // (new PayToPubKeyHashAddress($key->getPublicKey()->getPubKeyHash()))->getAddress();
            
            if(strtolower($symbol) == 'bch' && $params['bch-format'] != 'legacy') {
                $address = CashAddress::old2new($address);
            }

            $xprv = $key->isPrivate() ? $this->toExtendedKey($coin, $key, $network, $key_type) : null;
            $priv_wif = $key->isPrivate() ? $this->serializePrivKey($symbol, $network, $key->getPrivateKey()) : null;
            $pubkey = $key->getPublicKey()->getHex();
            $pubkeyhash = $key->getPublicKey()->getPubKeyHash()->getHex();
            $xpub = $this->toExtendedKey($coin, $key->withoutPrivateKey(), $network, $key_type);
        }
        else {
            throw new Exception("multisig keys not supported");
        }

        $addrs[] = array( 'xprv' => $xprv,
            'privkey' => $priv_wif,
            'pubkey' => $pubkey,
            'pubkeyhash' => $pubkeyhash,
            'xpub' => $xpub,
            'address' => $address,
            'index' => $index,
            'path' => $path);
    }

    function serializePrivKey($symbol, $network, $key) {
        $hex = strtolower($symbol) == 'eth';
        return $hex ? '0x' . $key->getHex() : $key->toWif($network);
    }

    
    private function address($key, $network) {
        $addrCreator = new AddressCreator();
        return $key->getAddress($addrCreator)->getAddress($network);
    }

    /*
     * Determines key type (x,y,Y,z,Z) based on coin/network and a key.
     */
    private function getKeyTypeFromCoinAndKey($coin, $key) {
        $nparams = $this->getNetworkParams($coin);
        $prefix = substr($key, 0, 4);

        // Parse the key to obtain prefix bytes.
        $s = new RawExtendedKeySerializer(Bitcoin::getEcAdapter());
        $rkp = $s->fromParser(new Parser(Base58::decodeCheck($key)));
        $key_prefix = '0x' . $rkp->getPrefix();

        $ext = $this->getExtendedPrefixes($coin);
        foreach($ext as $kt => $info) {
            if( $key_prefix  == strtolower(@$info['public']) ) {
                return $kt[0];
            }
            if( $key_prefix == strtolower(@$info['private']) ) {
                return $kt[0];
            }
        }
        throw new Exception("Keytype not found for $coin/$prefix");
    }

    private function getKeyTypeFromParams() {
        $params = $this->get_params();
        return $params['key-type'];
    }
    
    private function getSerializer($coin, $network, $key_type) {
        $adapter = Bitcoin::getEcAdapter();

        $prefix = $this->getScriptPrefixForKeyType($coin, $key_type);
        $config = new GlobalPrefixConfig([new NetworkConfig($network, [$prefix]),]);
//print_r($config); exit;  
        
        $serializer = new Base58ExtendedKeySerializer(new ExtendedKeySerializer($adapter, $config));
        return $serializer;
    }
        
    private function getSymbolAndNetwork($coin = null) {
        if(!$coin) {
            $params = $this->get_params();
            $coin = $params['coin'];
        }
        list($symbol, $network) = explode('-', $this->coinToChain($coin));
        // normalize values.
        return [strtoupper($symbol), strtolower($network)];
    }
    
    private function normalizeCoin($coin) {
        list($symbol, $net) = $this->getSymbolAndNetwork($coin);
        $suffix = $net == 'main' ? '' : '-' . $net;
        return "$symbol" . $suffix;
    }
    
    private function getNetworkParams($coin=null) {
        list($symbol, $net) = $this->getSymbolAndNetwork($coin);
        return coinparams::get_coin_network($symbol, $net);
    }
    
    private function getExtendedPrefixes($coin) {
        $params = $this->get_params();
        $nparams = $this->getNetworkParams($coin);
        if( @$params['alt-extended'] ) {
            $ext = @$params['alt-extended'];
            $val = @$nparams['prefixes']['extended']['alternates'][$ext];
            if(!$val) {
                throw new \Exception("Invalid value for --alt-extended.  Check coin type");
            }
        }
        else {
            $val = @$nparams['prefixes']['extended'];
            unset($val['alternates']);
        }
        $val = $val ?: [];
        // ensure no entries with empty values.
        foreach($val as $k => $v) {
            if(!@$v['public'] || !@$v['private']) {
                unset($val[$k]);
            }
        }
        return $val;
    }
    
    private function networkSupportsKeyType($network, $key_type, $coin) {
        if($key_type == 'z') {
            try {
                $network->getSegwitBech32Prefix();
            }
            catch(Exception $e) {
                return false;
            }
        }
        $nparams = $this->getNetworkParams($coin);
        $ext_prefixes = $this->getExtendedPrefixes($coin);
        $mcr = new MultiCoinRegistry($ext_prefixes);  // todo: cache these objects.
        return (bool)$mcr->prefixBytesByKeyType($key_type);        
    }
    
    // key_type is one of x,y,Y,z,Z
    private function getScriptDataFactoryForKeyType($key_type) {
        $helper = new KeyToScriptHelper(Bitcoin::getEcAdapter());
        
        $params = $this->get_params();
        $addr_type = $params['addr-type'];
        switch($addr_type) {
            case 'legacy':      return $helper->getP2pkhFactory();
            case 'p2sh-segwit': return $helper->getP2shFactory($helper->getP2wpkhFactory());
            case 'bech32':      return $helper->getP2wpkhFactory();
            case 'auto': break;  // use automatic detection based on key_type
            default:
                throw new Exception('Invalid value for addr_type');
                break;
        }
        
        // note: these calls are adapted from bitwasp slip132.php
        switch( $key_type ) {
            case 'x': $factory = $helper->getP2pkhFactory(); break;
            case 'X': $factory = $helper->getP2shFactory($helper->getP2pkhFactory()); break;  // also xpub.  this case won't work.
            case 'y': $factory = $helper->getP2shFactory($helper->getP2wpkhFactory()); break;
            case 'Y': $factory = $helper->getP2shP2wshFactory($helper->getP2pkhFactory()); break;
            case 'z': $factory = $helper->getP2wpkhFactory(); break;
            case 'Z': $factory = $helper->getP2wshFactory($helper->getP2pkhFactory()); break;
            default:
                throw new Exception("Unknown key type: $key_type");
        }
        return $factory;
    }
    
    // key_type is one of x,y,Y,z,Z
    private function getScriptPrefixForKeyType($coin, $key_type) {
        list($symbol, $net) = $this->getSymbolAndNetwork($coin);

        $params = $this->get_params();
        $addr_type = $params['addr-type'];
        
        $adapter = Bitcoin::getEcAdapter();
        $slip132 = new Slip132(new KeyToScriptHelper($adapter));
        $ext_prefixes = $this->getExtendedPrefixes($coin);
        // this allow user to force an address type.  Typically used
        // to generate segwit or bech32 addr from an xpub.
        
        if($addr_type != 'auto') {
            $ext_prefixes['xpub'] = $ext_prefixes[$key_type . 'pub'];
            $ext_prefixes['ypub'] = $ext_prefixes[$key_type . 'pub'];
            $ext_prefixes['zpub'] = $ext_prefixes[$key_type . 'pub'];
        }
        $coinPrefixes = new MultiCoinRegistry($ext_prefixes);
        
        switch($addr_type) {
            case 'legacy':      return $slip132->p2pkh($coinPrefixes);
            case 'p2sh-segwit': return $slip132->p2shP2wpkh($coinPrefixes);
            case 'bech32':      return $slip132->p2wpkh($coinPrefixes);
            case 'auto': break;  // use automatic detection based on key_type
            default:
                throw new Exception('Invalid value for addr_type');
                break;
        }
        
        switch( $key_type ) {
            case 'x': $prefix = $slip132->p2pkh($coinPrefixes); break;
            case 'X': $prefix = $slip132->p2shP2pkh($coinPrefixes); break;  // also xpub.  this case won't work.
            case 'y': $prefix = $slip132->p2shP2wpkh($coinPrefixes); break;
            case 'Y': $prefix = $slip132->p2shP2wshP2pkh($coinPrefixes); break;
            case 'z': $prefix = $slip132->p2wpkh($coinPrefixes); break;
            case 'Z': $prefix = $slip132->p2wshP2pkh($coinPrefixes); break;
            default:
                throw new Exception("Unknown key type: $key_type");
        }
        
        return $prefix;
    }
    
    private function toExtendedKey($coin, $key, $network, $key_type) {
        $serializer = $this->getSerializer($coin, $network, $key_type);
        return $serializer->serialize($network, $key);
    }
    
    private function fromExtended($coin, $extendedKey, $network, $key_type) {
        $serializer = $this->getSerializer($coin, $network, $key_type);
        return $serializer->parse($network, $extendedKey);
    }
    
    // converts a bip39 mnemonic string with optional password to an xprv key (string).
    public function mnemonicToKey($coin, $mnemonic, $key_type, $password = '')
    {
        $networkCoinFactory = new NetworkCoinFactory();
        $network = $networkCoinFactory->getNetworkCoinInstance($coin);
        Bitcoin::setNetwork($network);
        
        $seedGenerator = new Bip39SeedGenerator();

        // Derive a seed from mnemonic/password
        $password = $password === null ? '' : $password;
        $seed = $seedGenerator->getSeed($mnemonic, $password);
        
        $scriptFactory = $this->getScriptDataFactoryForKeyType($key_type);

        $bip32 = $this->hkf->fromEntropy($seed, $scriptFactory);
        return $this->toExtendedKey($coin, $bip32, $network, $key_type );
    }
    
    protected function genRandomSeed($password=null) {
        $params = $this->get_params();
        $num_bytes = (int)($params['gen-words'] / 0.75);
        
        // generate random mnemonic
        $random = new Random();
        $bip39 = MnemonicFactory::bip39();
        $entropy = $random->bytes($num_bytes);
        $mnemonic = $bip39->entropyToMnemonic($entropy);

        // generate seed and master priv key from mnemonic
        $seedGenerator = new Bip39SeedGenerator();
        $pw = $password == null ? '' : $password;
        $seed = $seedGenerator->getSeed($mnemonic, $pw);

        $data = [
            'seed' => $seed,
            'mnemonic' => $mnemonic,
        ];
        
        return $data;
    }
        
    protected function genKeysFromSeed($coin, $seedinfo) {
        $networkCoinFactory = new NetworkCoinFactory();
        $network = $networkCoinFactory->getNetworkCoinInstance($coin);
        Bitcoin::setNetwork($network);
        
                    // type   purpose        
        $key_types = ['x'  => 44,
                      'y'  => 49,
                      'z'  => 84,
//                      'Y'  => ??,    // multisig
//                      'Z'  => ??,    // multisig
                     ];
        $keys = [];
        
        $rows = [];
        foreach($key_types as $key_type => $purpose) {
            if( !$this->networkSupportsKeyType($network, $key_type, $coin) ) {
                // $data[$key_type] = null;
                continue;
            }
            $row = ['coin' => $this->normalizeCoin($coin),
                    'seed' => $seedinfo['seed']->getHex(),
                    'mnemonic' => $seedinfo['mnemonic']
                   ];
            
            $k = $key_type;
            $pf = '';
            
            $scriptFactory = $this->getScriptDataFactoryForKeyType($key_type);  // xpub

            $xkey = $this->hkf->fromEntropy($seedinfo['seed'], $scriptFactory);
            $masterkey = $this->toExtendedKey($coin, $xkey, $network, $key_type);
            $row[$pf . 'root-key'] = $masterkey;
    
            // determine bip32 path for ext keys, which requires a bip44 ID for coin.
            $bip32path = $this->getCoinBip44ExtKeyPathPurpose($coin, $purpose);
            if($bip32path) {
                // derive extended priv/pub keys.
                $prv = $xkey->derivePath($bip32path);
                $pub = $prv->withoutPrivateKey();
                $row[$pf . 'path'] = $bip32path;
                $row['xprv'] = $this->toExtendedKey($coin, $prv, $network, $key_type);
                $row['xpub'] = $this->toExtendedKey($coin, $pub, $network, $key_type);
                $row['comment'] = null;
            }
            else {
                $row[$pf . 'path'] = null;
                $row['xprv'] = null;
                $row['xpub'] = null;
                $row['comment'] = "Bip44 ID is missing for this coin, so extended keys not derived.";
            }
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function genRandomKeyForNetwork($coin) {
        $seedinfo = $this->genRandomSeed();
        return $this->genKeysFromSeed($coin, $seedinfo);
    }
    
    public function genRandomKeyForAllChains() {
        $seedinfo = $this->genRandomSeed();
        
        $allcoins = NetworkCoinFactory::getNetworkCoinsList();
        $rows = [];
        echo "Deriving keys... ";
        foreach($allcoins as $coin => $data) {
            echo "$coin, ";
            $rows = array_merge( $rows, $this->genKeysFromSeed($coin, $seedinfo));
        }
        echo "\n\n";
        return $rows;
    }
    
    public function coinToChain($coin) {
        return strstr($coin, '-') ? $coin : "$coin-main";
    }
    
    public function getCoinBip44($coin) {
        $map = CoinParams::get_all_coins();
        list($symbol, $net) = explode('-', $this->coinToChain($coin));
        $bip44 = @$map[strtoupper($symbol)][$net]['prefixes']['bip44'];
        return $bip44;
    }

    public function getCoinBip44ExtKeyPath($coin) {
        $bip44 = $this->getCoinBip44($coin);
        return is_int($bip44) ? sprintf("m/44'/%d'/0'/0", $bip44) : null;
    }
    
    public function getCoinBip44ExtKeyPathPurpose($coin, $purpose) {
        $bip44 = $this->getCoinBip44($coin);
        return is_int($bip44) ? sprintf("m/%s'/%d'/0'/0", $purpose, $bip44) : null;
    }
    
    public function getBip32PurposeByKeyType($key_type) {
        $map = ['x' => 44,
                'y' => 49,
                'z' => 84,
                'Y' => 141,
                'Z' => 141,
               ];
        return $map[$key_type];
    }

    public function getCoinBip44ExtKeyPathPurposeByKeyType($coin, $key_type) {
        $purpose = $this->getBip32PurposeByKeyType($key_type);
        return $this->getCoinBip44ExtKeyPathPurpose($coin, $purpose);
    }    
    
    private function getEthereumAddress(PublicKeyInterface $publicKey){
        static $pubkey_serializer = null;
        static $point_serializer = null;
        if(!$pubkey_serializer){
            $adapter = EcAdapterFactory::getPhpEcc(Bitcoin::getMath(), Bitcoin::getGenerator());
            $pubkey_serializer = new PublicKeySerializer($adapter);
            $point_serializer = new UncompressedPointSerializer(EccFactory::getAdapter());
        }

        $pubKey = $pubkey_serializer->parse($publicKey->getBuffer());
        $point = $pubKey->getPoint();
        $upk = $point_serializer->serialize($point);
        $upk = hex2bin(substr($upk, 2));

        $keccak = Keccak::hash($upk, 256);
        $eth_address_lower = strtolower(substr($keccak, -40));

        $hash = Keccak::hash($eth_address_lower, 256);
        $eth_address = '';
        for($i = 0; $i < 40; $i++) {
            // the nth letter should be uppercase if the nth digit of casemap is 1
            $char = substr($eth_address_lower, $i, 1);

            if(ctype_digit($char))
                $eth_address .= $char;
            else if('0' <= $hash[$i] && $hash[$i] <= '7')
                $eth_address .= strtolower($char);
            else 
                $eth_address .= strtoupper($char);
        }

        return '0x'. $eth_address;
    }
    
    
    /* Returns all columns available for reports
     */
    static public function all_cols()
    {
        return ['path', 'address', 'xprv', 'xpub', 'privkey', 'pubkey', 'pubkeyhash', 'index'];
    }

    /* Returns all columns available for reports when using --gen-key
     */
    static public function all_cols_genkey()
    {
        return ['coin', 'seed', 'mnemonic', 'root-key', 'path', 'xprv', 'xpub', 'comment'];
    }
    
    
    /* Returns default reporting columns
     */
    static public function default_cols()
    {
        return ['path', 'address', 'privkey'];
    }
    
    /* Returns default reporting columns when using --gen-key
     */
    static public function default_cols_genkey()
    {
        return ['coin', 'seed', 'mnemonic', 'root-key', 'path', 'xprv', 'xpub', 'comment'];
    }
    
}

// examples

//php hd-wallet-derive.php --coin=ltc -g --key=Ltpv79cjoATqwsPtgnVFa4AV3nrgCiCoPenqndoVYfyY1EmZuuMnD1DCEAbQE5NEpEBVbKXm786sygYFrR2WVnvfuG1znwDU9yDNvvNxn3nT9tx --numderive=5 --all-cols
//php hd-wallet-derive.php --coin=zec -g --key=xprv9zm6dDUb931Japtf1gMz4bw3CUBoAKULHzW3fRBs7zdmsDfVBZiSDDMYjzQqj3VvBPftNo54JCGoLwMo3nJeGHVDininxzffzpSVBnz2C95 --numderive=5
//php hd-wallet-derive.php --coin=bch -g --key=xprv9zcYpBfhcJzPwekgCraUG2KtgKKyQJeCXbHzwV9YjhtzEp1cSZzB9thR3S2ys6MzXuC2oBnW33VdauA1cCMm6pUZc8zHjQVzxCh1Ugt2H8p --numderive=5
//php hd-wallet-derive.php --key=xprvA1L51gQKdcH9LiV7HBN8MqHLoaNtQqPmhjJy6pLEJUDRRePGcdUpHVqfB2CgdWZUGjviNDA7EAsKmhJRXGQkbX4usEHRV4zhMhAFthJpAEQ --coin=dash --format=json --cols=all --loglevel=fatalerror --numderive=5 --startindex=0 -g
