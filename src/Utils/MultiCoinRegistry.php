<?php

namespace App\Utils;

use BitWasp\Bitcoin\Key\Deterministic\Slip132\PrefixRegistry;
use BitWasp\Bitcoin\Script\ScriptType;

class MultiCoinRegistry extends PrefixRegistry
{
    private $key_type_map;
    
    public function __construct($coinmeta)
    {
        $map = [];
        $t = [];
        $x = @$coinmeta['prefixes']['bip32'];
        $y = @$coinmeta['prefixes']['slip132']['ypub'];
        $Y = @$coinmeta['prefixes']['slip132']['Ypub'];
        $z = @$coinmeta['prefixes']['slip132']['zpub'];
        $Z = @$coinmeta['prefixes']['slip132']['Zpub'];
        
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
        
        $t[] = $x ? [ [$x['private'],$x['public']], $st['x'] ] : null;
        $t[] = $x ? [ [$x['private'],$x['public']], $st['X'] ] : null;
        $t[] = $y ? [ [$y['private'],$y['public']], $st['y'] ] : null;
        $t[] = $Y ? [ [$Y['private'],$Y['public']], $st['Y'] ] : null;
        $t[] = $z ? [ [$z['private'],$z['public']], $st['z'] ] : null;
        $t[] = $Z ? [ [$Z['private'],$Z['public']], $st['Z'] ] : null;
        
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
    
    public function prefixBytesByKeyType($key_type) {
        return @$this->key_type_map[$key_type];
    }
}
