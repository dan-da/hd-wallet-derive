<?php

namespace App\Utils;

use BitWasp\Bitcoin\Network\Network;
use BitWasp\Bitcoin\Script\ScriptType;
use coinParams\coinParams;

class FlexNetwork extends Network {
    
    protected $base58PrefixMap;
    protected $bip32PrefixMap;
    protected $bip32ScriptTypeMap;
    protected $signedMessagePrefix;
    protected $p2pMagic;
    
    function __construct($coin) {
        $network = 'main';
        if(strstr($coin, '-')) {
            list($coin, $network) = explode('-', $coin);
        }
        
        $params = coinParams::get_coin_network($coin, $network);
        
        $this->base58PrefixMap = [
            self::BASE58_ADDRESS_P2PKH => self::dh(@$params['prefixes']['public']),
            self::BASE58_ADDRESS_P2SH => self::dh(@$params['prefixes']['scripthash']),
            self::BASE58_WIF => self::dh(@$params['prefixes']['private']),
        ];
        
        $this->bip32PrefixMap = [
            // https://github.com/zcash/zcash/blob/master/src/chainparams.cpp#L146-L147
            self::BIP32_PREFIX_XPUB => self::th(@$params['prefixes']['bip32']['public'], true),
            self::BIP32_PREFIX_XPRV => self::th(@$params['prefixes']['bip32']['private'], true),
        ];
    
        $this->bip32ScriptTypeMap = [
            self::BIP32_PREFIX_XPUB => ScriptType::P2PKH,
            self::BIP32_PREFIX_XPRV => ScriptType::P2PKH,
        ];
    
        $this->signedMessagePrefix = $params['message_magic'];
    
        $this->p2pMagic = self::th(@$params['protocol']['magic']);
        
//        print_r($this); exit;
        
    }
    
    /** incoming values look like 0x1ec
     *  but bitwasp lib expects them like
     *  01ec or ec instead.  this method drops the 0x
     *  and prepends 0 if necessary to make length an even number.
     */
    static private function th($hex, $prepend_zero = false) {
        $hex = substr($hex, 2);
        $pre = strlen($hex) % 2 == 0 ? '' : '0';
        return $pre . $hex;
    }
    
    static private function dh($dec, $prepend_zero = false) {
        $hex = dechex($dec);
        $pre = strlen($hex) % 2 == 0 ? '' : '0';
        return $pre . $hex;
    }
    
    
    
}

