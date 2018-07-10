<?php

namespace App\Utils;

use BitWasp\Bitcoin\Key\Deterministic\Slip132\PrefixRegistry;
use BitWasp\Bitcoin\Script\ScriptType;

class MultiCoinRegistry extends PrefixRegistry
{
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
            'x-p2sh' => [ScriptType::P2SH, ScriptType::P2PKH],
            'y'  => [ScriptType::P2SH, ScriptType::P2WKH],
            'Y'  => [ScriptType::P2SH, ScriptType::P2WSH, ScriptType::P2PKH],
            'z'  => [ScriptType::P2WKH],
            'Z'  => [ScriptType::P2WSH, ScriptType::P2PKH],
        ];
        
        $t[] = $x ? [ [$x['private'],$x['public']], $st['x'] ] : null;
        $t[] = $x ? [ [$x['private'],$x['public']], $st['x-p2sh'] ] : null;
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
}
