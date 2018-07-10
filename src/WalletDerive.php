<?php

namespace App;

require_once __DIR__  . '/../vendor/autoload.php';

// For HD-Wallet Key Derivation
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bitcoin\Mnemonic\MnemonicFactory;
use Exception;
use App\Utils\NetworkCoinFactory;
use App\Utils\MyLogger;
use App\Utils\CashAddress;
use coinParams\coinParams;

// For ethereum addresses
use kornrunner\Keccak;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PublicKeyInterface;
use BitWasp\Bitcoin\Crypto\EcAdapter\Impl\PhpEcc\Serializer\Key\PublicKeySerializer;
use BitWasp\Bitcoin\Crypto\EcAdapter\EcAdapterFactory;
use Mdanter\Ecc\Serializer\Point\UncompressedPointSerializer;
use Mdanter\Ecc\EccFactory;

// For slip132
use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\GlobalPrefixConfig;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\NetworkConfig;
use BitWasp\Bitcoin\Key\Deterministic\Slip132\Slip132;
use BitWasp\Bitcoin\Key\KeyToScript\KeyToScriptHelper;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\Base58ExtendedKeySerializer;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\ExtendedKeySerializer;
use App\Utils\MultiCoinRegistry;


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
        
        $networkCoinFactory = new NetworkCoinFactory();
        $network = $networkCoinFactory->getNetworkCoinInstance($coin);
        Bitcoin::setNetwork($network);

        $master = $this->fromExtended($key, $network);

        $start = $params['startindex'];
        $end = $params['startindex'] + $params['numderive'];

        /*
         *  ROOT PATH INCLUSION
         * */
        if( $params['includeroot'] ) {
            $this->derive_key_worker($symbol, $network, $addrs, $master, null, 'm');
        }


        MyLogger::getInstance()->log( "Generating addresses", MyLogger::info );
        $path_base = is_numeric( $params['path']{0} ) ?  'm/' . $params['path'] : $params['path'];
        for($i = $start; $i < $end; $i++)
        {
            if($i && $i % 10 == 0)
            {
                MyLogger::getInstance()->log( "Generated $i keys", MyLogger::specialinfo );
            }
            $path = $path_base . "/$i";
            $key = $master->derivePath($path);
            
            $this->derive_key_worker($symbol, $network, $addrs, $key, $i, $path);
        }

        return $addrs;
    }
    
    private function derive_key_worker($symbol, $network, &$addrs, $key, $index, $path) {
        if(method_exists($key, 'getPublicKey')) {
            $address = strtolower($symbol) == 'eth' ?
                $address = $this->getEthereumAddress($key->getPublicKey()) :
                           $this->address($key, $network);
                // (new PayToPubKeyHashAddress($key->getPublicKey()->getPubKeyHash()))->getAddress();
            
            if(strtolower($symbol) == 'bch' && $params['bch-format'] != 'legacy') {
                $address = CashAddress::old2new($address);
            }

            $xprv = $key->isPrivate() ? $this->toExtendedKey($key, $network) : null;
            $priv_wif = $key->isPrivate() ? $key->getPrivateKey()->toWif($network) : null;
            $pubkey = $key->getPublicKey()->getHex();
            $pubkeyhash = $key->getPublicKey()->getPubKeyHash()->getHex();
            $xpub = $this->toExtendedKey($key->withoutPrivateKey(), $network);
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
    
    private function address($key, $network) {
        $addrCreator = new AddressCreator();
        return $key->getAddress($addrCreator)->getAddress($network);
    }
    
    private function getKeyType() {
        $params = $this->get_params();
        return $params['key'][0];
    }
    
    private function getSerializer($network) {
        $params = $this->get_params();
        $coin = strstr($params['coin'], '-') ? $params['coin'] : $params['coin'] . '-main';
        list($symbol, $net) = explode('-', $coin);
        $coinMeta = coinparams::get_coin_network($symbol, $net);
        
        $coinPrefixes = new MultiCoinRegistry($coinMeta);
        $adapter = Bitcoin::getEcAdapter();

        // If you want to produce different addresses,
        // set a different prefix/factory here.
        $slip132 = new Slip132(new KeyToScriptHelper($adapter));

        $key_type = $this->getKeyType();
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
        $config = new GlobalPrefixConfig([new NetworkConfig($network, [$prefix]),]);

        $serializer = new Base58ExtendedKeySerializer(new ExtendedKeySerializer($adapter, $config));
        return $serializer;
    }
    
    private function toExtendedKey($key, $network) {
        $serializer = $this->getSerializer($network);
        return $serializer->serialize($network, $key);
    }
    
    private function fromExtended($extendedKey, $network) {
        $serializer = $this->getSerializer($network);
        return $serializer->parse($network, $extendedKey);
    }

    // converts a bip39 mnemonic string with optional password to an xprv key (string).
    public function mnemonicToKey($coin, $mnemonic, $password = null)
    {
        $networkCoinFactory = new NetworkCoinFactory();
        $network = $networkCoinFactory->getNetworkCoinInstance($coin);
        Bitcoin::setNetwork($network);
        
//        $bip39 = MnemonicFactory::bip39();
        $seedGenerator = new Bip39SeedGenerator();

        // Derive a seed from mnemonic/password
        $password = $password === null ? '' : $password;
        $seed = $seedGenerator->getSeed($mnemonic, $password);
        
        // not logging seed.  just in case somebody keeps logs in insecure location.
        // mylogger()->log( "Seed: " . $seed->getHex(), mylogger::info );
        // echo $seed->getHex() . "\n";
        
        $bip32 = $this->hkf->fromEntropy($seed);
        return $bip32->toExtendedKey($network);
    }
    
    public function genRandomKeyForNetwork($coin) {
        $networkCoinFactory = new NetworkCoinFactory();
        $network = $networkCoinFactory->getNetworkCoinInstance($coin);
        Bitcoin::setNetwork($network);

        // generate random mnemonic
        $random = new Random();
        $bip39 = MnemonicFactory::bip39();
        $entropy = $random->bytes(64);
        $mnemonic = $bip39->entropyToMnemonic($entropy);
        
        // generate seed and master priv key from mnemonic
        $seedGenerator = new Bip39SeedGenerator();
        $seed = $seedGenerator->getSeed($mnemonic, '');
        $bip32 = $this->hkf->fromEntropy($seed);
        $masterkey = $bip32->toExtendedPrivateKey($network);

        // determine bip32 path for ext keys, which requires a bip44 ID for coin.
        $bip32path = $this->getCoinBip44ExtKeyPath($coin);
        if($bip32path) {
            // derive extended priv/pub keys.
            $ext_priv_key = $bip32->derivePath($bip32path)->toExtendedPrivateKey($network);
            $ext_pub_key = $bip32->derivePath($bip32path)->toExtendedPublicKey($network);
        }
        
        return [
            'coin' => $coin,
            'seed' => $seed->getHex(),
            'mnemonic' => $mnemonic,
            'master_priv_key' => $masterkey,
            'path' => @$bip32path ?: 'bip44 ID missing',
            'ext_priv_key' => @$ext_priv_key ?: 'bip44 ID missing',
            'ext_pub_key' => @$ext_pub_key ?: 'bip44 ID missing',
        ];
    }
    
    public function getCoinBip44($coin) {
        $map = coinParams::get_all_coins();
        $normal = strstr($coin, '-') ? $coin : "$coin-main";
        list($symbol, $net) = explode('-', $normal);
        $bip44 = @$map[strtoupper($symbol)][$net]['prefixes']['bip44'];
        return $bip44;
    }

    public function getCoinBip44ExtKeyPath($coin) {
        $bip44 = $this->getCoinBip44($coin);
        return is_int($bip44) ? sprintf("m/44'/%d'/0'/0", $bip44) : null;
    }
    
    public function genRandomKeyForAllNetworks() {
        $allcoins = NetworkCoinFactory::getNetworkCoinsList();
        $rows = [];
        foreach($allcoins as $coin => $data) {
            $rows[] = $this->genRandomKeyForNetwork($coin);
        }
        return $rows;
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

    /* Returns default reporting columns
     */
    static public function default_cols()
    {
        return ['path', 'address', 'privkey'];
    }
}

// examples

//php hd-wallet-derive.php --coin=ltc -g --key=Ltpv79cjoATqwsPtgnVFa4AV3nrgCiCoPenqndoVYfyY1EmZuuMnD1DCEAbQE5NEpEBVbKXm786sygYFrR2WVnvfuG1znwDU9yDNvvNxn3nT9tx --numderive=5 --all-cols
//php hd-wallet-derive.php --coin=zec -g --key=xprv9zm6dDUb931Japtf1gMz4bw3CUBoAKULHzW3fRBs7zdmsDfVBZiSDDMYjzQqj3VvBPftNo54JCGoLwMo3nJeGHVDininxzffzpSVBnz2C95 --numderive=5
//php hd-wallet-derive.php --coin=bch -g --key=xprv9zcYpBfhcJzPwekgCraUG2KtgKKyQJeCXbHzwV9YjhtzEp1cSZzB9thR3S2ys6MzXuC2oBnW33VdauA1cCMm6pUZc8zHjQVzxCh1Ugt2H8p --numderive=5
//php hd-wallet-derive.php --key=xprvA1L51gQKdcH9LiV7HBN8MqHLoaNtQqPmhjJy6pLEJUDRRePGcdUpHVqfB2CgdWZUGjviNDA7EAsKmhJRXGQkbX4usEHRV4zhMhAFthJpAEQ --coin=dash --format=json --cols=all --loglevel=fatalerror --numderive=5 --startindex=0 -g