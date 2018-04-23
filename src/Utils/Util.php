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
            'outfile:',
            'numderive:', 'startindex:',
            'includeroot',
            'path:',
            'format:', 'cols:',
            'logfile:', 'loglevel:',
            'list-cols',
            'version', 'help',
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

        if( isset($params['version']))
        {
            static::printVersion();
            return [$params, 2];
        }

        if(isset($params['help']) || !isset($params['g']) || !isset($params['coin']))
        {
            static::printHelp();
            return [$params, 1];
        }

        // TODO
        if(@$params['logfile'])
        {
            mylogger()->set_log_file( $params['logfile'] );
            mylogger()->echo_log = false;
        }

        $loglevel = @$params['loglevel'] ?: 'specialinfo';
        MyLogger::getInstance()->set_log_level_by_name( $loglevel );

        $key = @$params['key'];
        $mnemonic = @$params['mnemonic'];

        if( !$key && !$mnemonic ) {
            throw new Exception( "--key or --mnemonic must be specified." );
        }
        $params['mnemonic-pw'] = @$params['mnemonic-pw'] ?: null;

        if( @$params['path'] && !is_numeric($params['path']) && $params['path']{0} != 'm' ) {
            throw new Exception( "path parameter is invalid.  It should begin with m or an integer number.");
        }

        $params['cols'] = static::getCols( $params );
        if ( !isset( $params['path'] )){
            $params['path'] = 'm';
        }

        $params['format'] = @$params['format'] ?: 'txt';
        $params['cols'] = @$params['cols'] ?: 'all';
        $params['numderive'] = @$params['numderive'] ?: 10;
        $params['startindex'] = @$params['startindex'] ?: 0;
        $params['includeroot'] = isset($params['includeroot'] );

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
    
    --coin=<coin>        Coin (example btc or ltc) ( required )
    
    --key=<key>          xpriv or xpub key
    --mnemonic=<words>   bip39 seed words
                           note: either key or nmemonic is required.
                           
    --mnemonic-pw=<pw>   optionally specify password for mnemonic.

    --numderive=<n>      Number of keys to derive.  default=10

    --startindex=<n>     Index to start deriving keys from.  default=0
                            
    --cols=<cols>        a csv list of columns, or "all"
                         all:
                          ($allcols)
                         default:
                          ($defaultcols)

    --outfile=<path>     specify output file path.
    --format=<format>    txt|csv|json|jsonpretty|html|list|all   default=txt
    
                         if 'all' is specified then a file will be created
                         for each format with appropriate extension.
                         only works when outfile is specified.
                         
                         'list' prints only the first column. see --cols
                         
    --path=<path>        bip32 path to derive, relative to provided key (m).
                           eg "", "m/0" or "m/1"
                           default = "m"
                           
    --includeroot       include root key as first element of report.
    
    --logfile=<file>    path to logfile. if not present logs to stdout.
    --loglevel=<level>  $loglevels
                          default = info
    


END;

        fprintf( STDERR, $buf );

    }

    /* parses the --cols argument and returns an array of columns.
     */
    public static function getCols( $params )
    {
        $arg = static::stripWhitespace( @$params['cols'] ?: null );

        $allcols = WalletDerive::all_cols();

        if( $arg == 'all' ) {
            $cols = $allcols;
        }
        else if( !$arg ) {
            $cols = WalletDerive::default_cols();
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