<?php
/**
 * Created by PhpStorm.
 * User: massi
 * Date: 18-4-23
 * Time: 下午12:45
 */

namespace App\Utils;


use BitWasp\Bitcoin\Network\Network;
use BitWasp\Bitcoin\Network\Networks\Bitcoin;
use BitWasp\Bitcoin\Network\Networks\Dash;
use BitWasp\Bitcoin\Network\Networks\Litecoin;
use BitWasp\Bitcoin\Network\Networks\Zcash;

class NetworkCoinFactory extends Network
{
    public static function getNetworkCoinsList() {
        return [
            // alpha, by symbol.
            'bch'  => 'Bitcoin Cash',
            'btc'  => 'Bitcoin',
            'dash' => 'Dash',
            'ltc'  => 'Litecoin',
            'zec'  => 'ZCash',
        ];
    }
    
    public static function getNetworkCoinInstance($coin)
    {
        switch($coin)
        {
            case 'ltc':

                return new Litecoin();

                break;

            case 'zec':

                return new Zcash();

                break;

            case 'dash':

                return new Dash();

                break;

            default:
                // for BTC and BCH, the dafult Network is already used
                return new Bitcoin();
                break;
        }
    }
}