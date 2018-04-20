<?php

namespace App;

require_once __DIR__  . '/../vendor/autoload.php';

// For HD-Wallet Key Derivation
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Key\Factory\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Network\Networks\Litecoin;
use BitWasp\Bitcoin\Network\Networks\Zcash;

// For Bip39 Mnemonics
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bitcoin\Mnemonic\MnemonicFactory;


/* A class that implements HD wallet key/address derivation
 */
class WalletDerive
{

    // Contains options we care about.
    protected $params;
    
    public function __construct($params)
    {
        $this->params = $params;
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

        $addrs = array();

        switch($coin)
        {
            case 'ltc':

                Bitcoin::setNetwork(new Litecoin());

                break;

            case 'zec':

                Bitcoin::setNetwork(new Zcash());

                break;

            default:
                break;
        }

        $network = Bitcoin::getNetwork();


        $HKF = new HierarchicalKeyFactory();

        $master = $HKF->fromExtended($key, $network);

        $start = $params['startindex'];
        $end = $params['startindex'] + $params['numderive'];

        $bcashaddress = '';

        if( $params['includeroot'] ) {
            $ptpkha = new PayToPubKeyHashAddress($master->getPublicKey()->getPubKeyHash());
            $address = $ptpkha->getAddress();
            if($coin == 'bcc')
            {
                $bcashaddress = CashAddress::old2new($address);
            }
            $xprv = $master->isPrivate() ? $master->toExtendedKey($network) : null;
            $wif = $master->isPrivate() ? $master->getPrivateKey()->toWif($network) : null;
            $pubkey = $master->getPublicKey()->getHex();
            $pubkeyhash = $master->getPublicKey()->getPubKeyHash()->getHex();
            $xpub = $master->toExtendedPublicKey($network);


            $addrs[] = array( 'xprv' => $xprv,
                              'privkey' => $wif,
                              'pubkey' => $pubkey,
                              'pubkeyhash' => $pubkey,
                              'xpub' => $xpub,
                              'address' => $address,
                              'bitcoincash' => '',
                              'index' => null,
                              'path' => 'm');

            if($coin == 'bcc')
            {
                $addrs[] = array( 'xprv' => $xprv,
                    'privkey' => $wif,
                    'pubkey' => $pubkey,
                    'pubkeyhash' => $pubkey,
                    'xpub' => $xpub,
                    'address' => $address,
                    'bitcoincash' => $bcashaddress,
                    'index' => null,
                    'path' => 'm');

            }
        }


        MyLogger::getInstance()->log( "Generating addresses", MyLogger::info );
        $path_base = is_numeric( $params['path']{0} ) ?  'm/' . $params['path'] : $params['path'];
        for( $i = $start; $i < $end; $i ++ ) {
            if( $i && $i % 10 == 0 ) {
                MyLogger::getInstance()->log( "Generated $i keys", MyLogger::specialinfo );
            }
            $path = $path_base . "/$i";
            $key = $master->derivePath($path);
            
            // fixme: hack for copay/multisig.  maybe should use a callback?
            if(method_exists($key, 'getPublicKey')) {
                // bip32 path
                $ptpkha = new PayToPubKeyHashAddress($key->getPublicKey()->getPubKeyHash());

                $address = $ptpkha->getAddress();

                if($coin == 'bcc')
                {
                    $bcashaddress = CashAddress::old2new($address);
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
                'bitcoincash' => '',
                'index' => $i,
                'path' => $path);

            if($coin == 'bcc')
            {
                $addrs[] = array( 'xprv' => $xprv,
                    'privkey' => $priv_wif,
                    'pubkey' => $pubkey,
                    'pubkeyhash' => $pubkeyhash,
                    'xpub' => $xpub,
                    'address' => $address,
                    'bitcoincash' => $bcashaddress,
                    'index' => $i,
                    'path' => $path);

            }
        }

        return $addrs;
    }

    // converts a bip39 mnemonic string with optional password to an xprv key (string).
    static public function mnemonicToKey($mnemonic, $password=null) {
        $bip39 = MnemonicFactory::bip39();
        $seedGenerator = new Bip39SeedGenerator($bip39);

        // Derive a seed from mnemonic/password
        $seed = $seedGenerator->getSeed($mnemonic, $password);
        
        // not logging seed.  just in case somebody keeps logs in insecure location.
        // mylogger()->log( "Seed: " . $seed->getHex(), mylogger::info );
        // echo $seed->getHex() . "\n";
        
        $bip32 = \BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory::fromEntropy($seed);
        return $bip32->toExtendedKey();
    }

    /* Returns all columns available for reports
     */
    static public function all_cols() {
        return ['path', 'address', 'bitcoincash', 'xprv', 'xpub', 'privkey', 'pubkey', 'pubkeyhash', 'index'];
    }

    /* Returns default reporting columns
     */
    static public function default_cols() {
        return ['path', 'address', 'privkey'];
    }
}


