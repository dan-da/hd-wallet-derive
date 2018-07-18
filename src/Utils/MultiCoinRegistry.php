<?php

namespace App\Utils;

use BitWasp\Bitcoin\Key\Deterministic\Slip132\PrefixRegistry;
use BitWasp\Bitcoin\Script\ScriptType;

class MultiCoinRegistry extends PrefixRegistry
{
    private $key_type_map;

    /**
     * extended_map should look like:
     * {
     *    "xpub": {
     *      "public": "0xXXXXXXXX",
     *      "private": "0xXXXXXXXX"
     *    },
     *    "ypub": {
     *      "public": "0xXXXXXXXX",
     *      "private": "0xXXXXXXXX"
     *    },
     *    "zpub": {
     *      "public": "0xXXXXXXXX",
     *      "private": "0xXXXXXXXX"
     *    },
     *    "Ypub": {
     *      "public": "0xXXXXXXXX",
     *      "private": "0xXXXXXXXX"
     *    },
     *    "Zpub": {
     *      "public": "0xXXXXXXXX",
     *      "private": "0xXXXXXXXX"
     *    }
     * }
     */
    public function __construct($extended_map)
    {
        $em = $extended_map;
        $map = [];
        $t = [];
        $x = @$em['xpub'];
        $y = @$em['ypub'];
        $Y = @$em['Ypub'];
        $z = @$em['zpub'];
        $Z = @$em['Zpub'];
        
        $st = [
            'x'  => [ScriptType::P2PKH],
            'X'  => [ScriptType::P2SH, ScriptType::P2PKH],   // p2pkh in p2sh (typically multisig).  normally in xpub instead.
            'y'  => [ScriptType::P2SH, ScriptType::P2WKH],
            'Y'  => [ScriptType::P2SH, ScriptType::P2WSH, ScriptType::P2PKH],
            'z'  => [ScriptType::P2WKH],
            'Z'  => [ScriptType::P2WSH, ScriptType::P2PKH],
        ];
        
        // to indicate if each prefix is supported by this network or not.
        $this->key_type_map = [
            'x'  => $x,
            'X'  => $x,   // p2pkh in p2sh (typically multisig).  normally in xpub instead.
            'y'  => $y,
            'Y'  => $Y,
            'z'  => $z,
            'Z'  => $Z,
        ];
        
        $t[] = $this->v($x) ? [ [$x['private'],$x['public']], $st['x'] ] : null;
        $t[] = $this->v($x) ? [ [$x['private'],$x['public']], $st['X'] ] : null;
        $t[] = $this->v($y) ? [ [$y['private'],$y['public']], $st['y'] ] : null;
        $t[] = $this->v($Y) ? [ [$Y['private'],$Y['public']], $st['Y'] ] : null;
        $t[] = $this->v($z) ? [ [$z['private'],$z['public']], $st['z'] ] : null;
        $t[] = $this->v($Z) ? [ [$Z['private'],$Z['public']], $st['Z'] ] : null;
        
        foreach ($t as $row) {
            if(!$row) {
                continue;
            }
            list ($prefixList, $scriptType) = $row;
            foreach($prefixList as &$val) {
                // Slip132\PrefixRegistry expects 8 byte hex values, without 0x prefix.
                $val = str_replace('0x', '', $val);
            }
            $type = implode("|", $scriptType);
            $map[$type] = $prefixList;
        }
        parent::__construct($map);
    }
    
    private function v($kt) {
        return @$kt['private'] && $kt['public']; 
    }
    
    public function prefixBytesByKeyType($key_type) {
        return @$this->key_type_map[$key_type];
    }
}
