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
use coinParams\coinParams;

class NetworkCoinFactory extends Network
{
    public static function getNetworkCoinsList() {
        
        $coins = coinParams::get_all_coins();
        
        $list = [];
        foreach($coins as $sym => $c) {
            foreach($c as $net => $info) {
                if(!@$info['prefixes']['bip32']['public'] ||
                   !@$info['prefixes']['bip32']['private'] ) {
                    continue;
                }
                $suffix = $net == 'main' ? '' : "-$net";
                $symbol = $sym . $suffix;
                $list[$symbol] = $info['name'];
            }
        }
        return $list;
    }
    
    public static function getNetworkCoinInstance($coin)
    {
        return new FlexNetwork($coin);
    }
}
