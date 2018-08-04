<?php
/**
 * Created by PhpStorm.
 * User: massi
 * Date: 18-4-23
 * Time: 下午12:45
 */

namespace App\Utils;


use BitWasp\Bitcoin\Network\Network;
use BitWasp\Bitcoin\Network\NetworkFactory;
use CoinParams\CoinParams;

class NetworkCoinFactory extends Network
{
    public static function getNetworkCoinsList() {
        
        $coins = CoinParams::get_all_coins();
        
        $list = [];
        foreach($coins as $sym => $c) {
            foreach($c as $net => $info) {
                if(!@$info['prefixes']['extended']['xpub']['public'] ||
                   !@$info['prefixes']['extended']['xpub']['private'] ) {
                    continue;
                }
                $suffix = $net == 'main' ? '' : "-$net";
                $symbol = $sym . $suffix;
                $list[$symbol] = ['name' => $info['name'],
                                  'bip44' => $info['prefixes']['bip44']];
            }
        }
        return $list;
    }
    
    public static function getNetworkCoinInstance($coin)
    {
        return new FlexNetwork($coin);
    }
}
