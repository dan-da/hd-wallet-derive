<?php
/**
 * Created by PhpStorm.
 * User: massi
 * Date: 18-4-23
 * Time: 下午1:14
 */

namespace App\Utils;


use Exception;
use App\WalletDerive;


class Util
{

    // returns the CLI params, exactly as entered by user.
    public static function getCliParams()
    {
        $paramsArray = array( 'key:',
            'coin:',
            'mnemonic:',
            'mnemonic-pw:',
            'key-type:',
            'addr-type:',
            'outfile:',
            'numderive:', 'startindex:',
            'includeroot',
            'path:',
            'format:', 'cols:',
            'logfile:', 'loglevel:',
            'list-cols',
            'bch-format:',
            'alt-extended:',
            'gen-key', 'gen-key-all',
            'version', 'help', 'helpcoins',
        );

        $params = getopt( 'g', $paramsArray);

        return $params;
    }

    /* processes and sanitizes the CLI params. adds defaults
     * and ensure each value is set.
     */
    public static function processCliParams()
    {

        $params = static::getCliParams();

        $success = 0;   // 0 == success.

        if( isset($params['version'])) {
            static::printVersion();
            return [$params, 2];
        }

        // format and cols must be set prior to calling ::printHelpCoins()
        $params['format'] = @$params['format'] ?: 'txt';
        $params['cols'] = @$params['cols'] ?: 'all';
        
        if(isset($params['helpcoins'])) {
            static::printHelpCoins( $params );
            return [$params, 1];
        }
        
        if(isset($params['help']) || !isset($params['g'])) {
            static::printHelp();
            return [$params, 1];
        }
        
        // default to btc for backwards compat.
        $params['coin'] = @$params['coin'] ?: 'btc';
        
        // TODO
        if(@$params['logfile']) {
            mylogger()->set_log_file( $params['logfile'] );
            mylogger()->echo_log = false;
        }

        $loglevel = @$params['loglevel'] ?: 'specialinfo';
        MyLogger::getInstance()->set_log_level_by_name( $loglevel );

        $key = @$params['key'];
        $mnemonic = @$params['mnemonic'];

        if( !$key && !$mnemonic && !isset($params['gen-key'])) {
            throw new Exception( "--key or --mnemonic or --gen-key must be specified." );
        }
        $params['mnemonic-pw'] = @$params['mnemonic-pw'] ?: null;

        $params['addr-type'] = @$params['addr-type'] ?: 'auto';
        $allowed_addr_type = ['legacy', 'p2sh-segwit', 'bech32', 'auto'];
        if(!in_array($params['addr-type'], $allowed_addr_type)) {
            throw new Exception(sprintf("--addr-type must be one of: [%s]", implode('|', $allowed_addr_type)));
        }
        
        $keytype = @$params['key-type'] ?: 'x';
        $keytypes = ['x', 'y', 'z'];  // , 'Y', 'Z'];
        if(!in_array($keytype, $keytypes ) ) {
            throw new Exception( "--key-type must be one of: " . implode(',', $keytypes ));
        }
        $params['key-type'] = $keytype;
        
        if( @$params['path'] ) {
            if(!preg_match('/[m\d]/', $params['path'][0]) ) {
                throw new Exception( "path parameter is invalid.  It should begin with m or an integer number.");
            }
            if(!preg_match("#^[/\dx']*$#", @substr($params['path'], 1) ) ) {
                throw new Exception( "path parameter is invalid.  It should begin with m or an integer and contain only [0-9'/x]");
            }
            if(preg_match('#//#', $params['path']) ) {
                throw new Exception( "path parameter is invalid.  It must not contain '//'");
            }
            if(preg_match("#/[0-9']*?x[0-9']*?/#", $params['path']) ) {
                throw new Exception( "path parameter is invalid.  x in wrong position");
            }
            if(preg_match("#/x[^']+.*$#", $params['path']) ) {
                throw new Exception( "path parameter is invalid.  only x or x' allowed in final path segment");
            }            
            if(preg_match("#/'#", $params['path']) ) {
                throw new Exception( "path parameter is invalid. single-quote must follow an integer");
            }
            if(preg_match("#''#", $params['path']) ) {
                throw new Exception( "path parameter is invalid. It must not contain \"''\"");
            }
            $params['path'] = rtrim($params['path'], '/');  // trim any trailing path separator.
        }

        $params['cols'] = static::getCols( $params );
        if ( !isset( $params['path'] )) {
            $params['path'] = 'm';
        }

        $params['bch-format'] = @$params['bch-format'] ?: 'cash';
        $params['numderive'] = @$params['numderive'] ?: 10;
        $params['alt-extended'] = @$params['alt-extended'] ?: null;
        $params['startindex'] = @$params['startindex'] ?: 0;
        $params['includeroot'] = isset($params['includeroot'] );
        $params['gen-key'] = isset($params['gen-key']);
        $params['gen-key-all'] = isset($params['gen-key-all']);  // hidden param, for the truly worthy who read the code.

        return [$params, $success];
    }

    /**
     * prints program version text
     */
    public static function printVersion()
    {
        $versionFile = __DIR__ . '/../VERSION';

        $version = @file_get_contents($versionFile);
        echo $version ?: 'version unknown' . "\n";
    }


    /* prints CLI help text
     */
    public static function printHelp()
    {

        $levels = MyLogger::getInstance()->get_level_map();
        $allcols = implode(',', WalletDerive::all_cols() );
        $defaultcols = implode(',', WalletDerive::default_cols() );
        
        $loglevels = implode(',', array_values( $levels ));

        $buf = <<< END

   hd-wallet-derive.php

   This script derives private keys and public addresses

   Options:

    -g                   go!  ( required )
        
    --key=<key>          xpriv or xpub key
    --mnemonic=<words>   bip39 seed words
                           note: either key or nmemonic is required.
                           
    --mnemonic-pw=<pw>   optional password for mnemonic.
    
    --addr-type=<t>      legacy | p2sh-segwit | bech32 | auto
                            default = auto  (based on key-type)
    
    --key-type=<t>       x | y | z
                            default = x. applies to --mnemonic only.
                            
    --coin=<coin>        Coin Symbol ( default = btc )
                         See --helpcoins for a list.
                         
    --helpcoins          List all available coins/networks.
                         --format applies to output.
    
    --numderive=<n>      Number of keys to derive.  default=10

    --startindex=<n>     Index to start deriving keys from.  default=0
                            
    --cols=<cols>        a csv list of columns, or "all"
                         all:
                          ($allcols)
                         default:
                          ($defaultcols)

    --bch-format=<fmt>   Bitcoin cash address format.
                           legacy|cash   default=cash
    --alt-extended=<id>  Use alternate extended keys. supported:
                           LTC:  Ltub
                           
    --outfile=<path>     specify output file path.
    --format=<format>    txt|md|csv|json|jsonpretty|html|list|all   default=txt
    
                         if 'all' is specified then a file will be created
                         for each format with appropriate extension.
                         only works when outfile is specified.
                         
                         'list' prints only the first column. see --cols
                         
    --path=<path>        bip32 path to derive, relative to provided key (m).
                           ex: "", "m/0", "m/1"
                           default = "m"
                             if --mnemonic is used, then default is the
                             bip44 path to extended key, eg m/44'/0'/0'/0
                             which facilitates address derivation from
                             mnemonic phrase.
                           note: /x' at end generates hardened addresses.
                           ex: m/0/x'", "m/1/x'"
                           for bitcoin-core hd-wallet use: m/0'/0'/x'
                           
    --includeroot       include root key as first element of report.
    --gen-key           generates a new key.
    
    --logfile=<file>    path to logfile. if not present logs to stdout.
    --loglevel=<level>  $loglevels
                          default = info
    


END;

        fprintf( STDERR, $buf );

    }
    
    public static function printHelpCoins( $params ) {
        $allcoins = NetworkCoinFactory::getNetworkCoinsList();

        $data = [];
        foreach($allcoins as $k => $v) {
            $data[] = ['Symbol' => $k,
                       'Coin / Network' => $v['name'],
                       'Bip44' => $v['bip44']];
        }
        
        $summary = [];
        WalletDeriveReport::print_results_worker($summary, $data, null, $params['format']);
        echo "\n\n";
    }

    /* parses the --cols argument and returns an array of columns.
     */
    public static function getCols( $params )
    {
        $arg = static::stripWhitespace( @$params['cols'] ?: null );

        $allcols = isset($params['gen-key']) ? WalletDerive::all_cols_genkey() : WalletDerive::all_cols();

        if( $arg == 'all' ) {
            $cols = $allcols;
        }
        else if( !$arg ) {
            $cols = isset($params['gen-key']) ? WalletDerive::default_cols_genkey() : WalletDerive::default_cols();
        }
        else {
            $cols = explode( ',', $arg );
            foreach( $cols as $c ) {
                if( !in_array($c, $allcols) )
                {
                    throw new Exception( "'$c' is not a known report column.", 2 );
                }
            }
        }

        return $cols;
    }


    /* removes whitespace from a string
     */
    public static function stripWhitespace( $str )
    {
        return preg_replace('/\s+/', '', $str);
    }
}