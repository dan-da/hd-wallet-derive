#!/usr/bin/env php
<?php

/**
 * Entry point for hd-wallet-derive.
 *
 * Code in this file is related to interacting with the shell.
 */

// Let's be strict about things.
require_once __DIR__ . '/vendor/autoload.php';
\strictmode\initializer::init();


use App\Utils\MyLogger;
use App\WalletDerive;
use App\Utils\WalletDeriveReport;
use App\Utils\Util;



/**
 * Our main function.  It performs top-level exception handling.
 */
function main()
{
    // why limit ourselves?    ;-)
    ini_set('memory_limit', -1 );

    try
    {
        // CLI Parameters processing
        $orig_params = Util::getCliParams();
        list( $params, $success ) = Util::processCliParams();
        if( $success != 0 )
        {
            return $success;
        }

        // Creates WalletDerive object
        $walletDerive = new WalletDerive($params);
        if($params['gen-key-all']) {
            $result = $walletDerive->genRandomKeyForAllChains();
            WalletDeriveReport::printResults($params, $result, true);
            return 0;
        }
        if($params['gen-key']) {
            $result = $walletDerive->genRandomKeyForNetwork($params['coin']);
            WalletDeriveReport::printResults($params, $result);
            return 0;
        }

        // Key derived from mnemonic if mnemonic is choosen
        if( !@$params['key'] && @$params['mnemonic'] && !@$orig_params['path'] && !@$orig_params['preset']) {
            $path = $walletDerive->getCoinBip44ExtKeyPathPurposeByKeyType($params['coin'], $params['key-type']);
            if($path) {
                $params['path'] = $path;
                $walletDerive = new WalletDerive($params);
            }
            else {
                throw new Exception(sprintf("Bip32 extended key path unknown because no Bip44 ID found for %s.  You can override by setting --path explicitly.", $params['coin']));
            }
        }
        $key = @$params['key'] ?: $walletDerive->mnemonicToKey($params['coin'], $params['mnemonic'], $params['key-type'], $params['mnemonic-pw']);
        $addrs = $walletDerive->derive_keys($key);

        // Prints result
        echo "\n";
        WalletDeriveReport::printResults($params, $addrs);
        return 0;
    }
    catch(Exception $e)
    {
        MyLogger::getInstance()->log_exception( $e );
        // print validation errors to stderr.
        if( $e->getCode() == 2 ) {
            fprintf( STDERR, $e->getMessage() . "\n\n" );
        }
        return $e->getCode() ?: 1;
    }
}

exit(main());
