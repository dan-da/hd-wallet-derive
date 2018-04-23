<?php
/**
 * Created by PhpStorm.
 * User: massi
 * Date: 18-4-23
 * Time: 下午12:45
 */

namespace App\Utils;


use BitWasp\Bitcoin\Network\Network;
use BitWasp\Bitcoin\Network\Networks\Litecoin;
use BitWasp\Bitcoin\Network\Networks\Zcash;

class NetworkCoinFactory extends Network
{
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

            default:
                // for BTC and BCC, the dafult Network is already used
                break;
        }
    }
}