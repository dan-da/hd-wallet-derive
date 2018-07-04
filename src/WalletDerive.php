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

        $coin = $params['coin'];
        list($symbol) = explode('-', $coin);

        $addrs = array();

        $networkCoinFactory = new NetworkCoinFactory();
        $networkCoin = $networkCoinFactory->getNetworkCoinInstance($coin);

        Bitcoin::setNetwork($networkCoin);

        $network = Bitcoin::getNetwork();

        $master = $this->hkf->fromExtended($key, $network);

        $start = $params['startindex'];
        $end = $params['startindex'] + $params['numderive'];

        /*
         *  ROOT PATH INCLUSION
         *
         *
         *
         *
         *
         * */
        if( $params['includeroot'] ) {
            $ptpkha = new PayToPubKeyHashAddress($master->getPublicKey()->getPubKeyHash());
            $address = $ptpkha->getAddress();

            if(strtolower($symbol) == 'bch' && $params['bch-format'] != 'legacy') {
                $address = CashAddress::old2new($address);
            }
            
            $xprv = $master->isPrivate() ? $master->toExtendedKey($network) : null;
            $wif = $master->isPrivate() ? $master->getPrivateKey()->toWif($network) : null;
            $pubkey = $master->getPublicKey()->getHex();
            $xpub = $master->toExtendedPublicKey($network);

            $addrs[] = array( 'xprv' => $xprv,
                              'privkey' => $wif,
                              'pubkey' => $pubkey,
                              'pubkeyhash' => $pubkey,
                              'xpub' => $xpub,
                              'address' => $address,
                              'index' => null,
                              'path' => 'm');

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
            

            if(method_exists($key, 'getPublicKey')) {
                // bip32 path
                $ptpkha = new PayToPubKeyHashAddress($key->getPublicKey()->getPubKeyHash());

                $address = $ptpkha->getAddress();
                if(strtolower($symbol) == 'bch' && $params['bch-format'] != 'legacy') {
                    $address = CashAddress::old2new($address);
                }

                $xprv = $key->isPrivate() ? $key->toExtendedKey($network) : null;
                $priv_wif = $key->isPrivate() ? $key->getPrivateKey()->toWif($network) : null;
                $pubkey = $key->getPublicKey()->getHex();
                $pubkeyhash = $key->getPublicKey()->getPubKeyHash()->getHex();
                $xpub = $key->toExtendedPublicKey($network);
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
                'index' => $i,
                'path' => $path);
        }

        return $addrs;
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
        
        $random = new Random();
        
        $bip39 = MnemonicFactory::bip39();
        $entropy = $random->bytes(64);
        $mnemonic = $bip39->entropyToMnemonic($entropy);
        $seedGenerator = new Bip39SeedGenerator();
        $seed = $seedGenerator->getSeed($mnemonic, '');
        $bip32 = $this->hkf->fromEntropy($seed);
        
        return [
            'coin' => $coin,
            'seed' => $seed->getHex(),
            'mnemonic' => $mnemonic,
            'master_priv_key' => $bip32->toExtendedPrivateKey($network),
        ];
    }

    public function genRandomKeyForAllNetworks() {
        $allcoins = NetworkCoinFactory::getNetworkCoinsList();
        $rows = [];
        foreach($allcoins as $coin => $data) {
            $rows[] = $this->genRandomKeyForNetwork($coin);
        }
        return $rows;
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