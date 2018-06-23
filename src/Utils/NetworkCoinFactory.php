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

class NetworkCoinFactory extends Network
{
    public static function getNetworkCoinsList() {
        return [
            // alpha, by symbol.
            'bch'          => 'Bitcoin Cash',
            'bch-testnet'  => 'Bitcoin Cash Testnet',
            'btc'          => 'Bitcoin',
            'btc-testnet'  => 'Bitcoin Testnet',
            'btc-regtest'  => 'Bitcoin Regtest',
            'dash'         => 'Dash',
            'dash-testnet' => 'Dash Testnet',
            'doge'         => 'Dogecoin',
            'doge-testnet' => 'Dogecoin Testnet',
            'ltc'          => 'Litecoin',
            'ltc-testnest' => 'Litecoin Testnet',
            'via'          => 'Viacoin',
            'via-testnest' => 'Viacoin Testnet',
            'zec'          => 'ZCash',
        ];
    }
    
    public static function getNetworkCoinInstance($coin)
    {
        switch($coin)
        {
            case 'bch'          : return NetworkFactory::bitcoin();  // bch is a special case and uses bitcoin network.
            case 'bch-testnet'  : return NetworkFactory::bitcoinTestnet();
            case 'btc'          : return NetworkFactory::bitcoin();
            case 'btc-testnet'  : return NetworkFactory::bitcoinTestnet();
            case 'btc-regtest'  : return NetworkFactory::bitcoinRegtest();
            case 'dash'         : return NetworkFactory::dash();
            case 'dash-testnet' : return NetworkFactory::dashTestnet();
            case 'doge'         : return NetworkFactory::dogecoin();
            case 'doge-testnet' : return NetworkFactory::dogecoinTestnet();
            case 'ltc'          : return NetworkFactory::litecoin();
            case 'ltc-testnet'  : return NetworkFactory::litecoinTestnet();
            case 'via'          : return NetworkFactory::viacoin();
            case 'via-testnet'  : return NetworkFactory::viacoinTestnet();
            case 'zec'          : return NetworkFactory::zcash();
                
            default:
                throw new Exception("Coin '$coin' is unrecognized");
        }
    }
}