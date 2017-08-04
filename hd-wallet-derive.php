#!/usr/bin/env php
<?php

/**
 * Entry point for hd-wallet-derive.
 *
 * Code in this file is related to interacting with the shell.
 */

// Let's be strict about things.
require_once __DIR__ . '/lib/strict_mode.funcs.php';

// This guy does the heavy lifting.
require_once __DIR__ . '/lib/wallet_derive.class.php';

/**
 * Call main and exit with return code.
 */
exit(main($argv));

/**
 * Our main function.  It performs top-level exception handling.
 */
function main( $argv ) {
    // why limit ourselves?    ;-)
    ini_set('memory_limit', -1 );

    try {
        list( $params, $success ) = process_cli_params( get_cli_params( $argv ));
        if( $success != 0 ) {
            return $success;
        }

        $worker = new wallet_derive( $params );

        $key = @$params['key'] ?: $worker->mnemonicToKey( $params['mnemonic'], $params['mnemonic-pw'] );
        $addrs = $worker->derive_keys($key);
        
        echo "\n";
        walletderivereport::print_results($params, $addrs);
        return 0;
    }
    catch( Exception $e ) {
        mylogger()->log_exception( $e );
        
        // print validation errors to stderr.
        if( $e->getCode() == 2 ) {
            fprintf( STDERR, $e->getMessage() . "\n\n" );
        }
        return $e->getCode() ?: 1;
    }
}

/* returns the CLI params, exactly as entered by user.
 */
function get_cli_params() {
    $params = getopt( 'g', array( 'key:',
                                  'mnemonic:',
                                  'mnemonic-pw:',
                                  'outfile:',
                                  'numderive:',
                                  'includeroot',
                                  'path:',
                                  'format:', 'cols:',
                                  'logfile:', 'loglevel:',
                                  'list-cols',
                                  'version', 'help',
                                  ) );        

    return $params;
}

/* processes and sanitizes the CLI params. adds defaults
 * and ensure each value is set.
 */
function process_cli_params( $params ) {
    $success = 0;   // 0 == success.
    
    if( isset( $params['version'] ) ) {
        print_version();
        return [$params, 2];
    }
    if( isset( $params['help']) || !isset($params['g']) ) {
        print_help();
        return [$params, 1];
    }
    
    if( @$params['logfile'] ) {
        mylogger()->set_log_file( $params['logfile'] );
        mylogger()->echo_log = false;
    }

    $loglevel = @$params['loglevel'] ?: 'specialinfo';
    mylogger()->set_log_level_by_name( $loglevel );

    $key = @$params['key'];
    $mnemonic = @$params['mnemonic'];
    
    if( !$key && !$mnemonic ) {
        throw new Exception( "--key or --mnemonic must be specified." );
    }
    $params['mnemonic-pw'] = @$params['mnemonic-pw'] ?: null;
    
    if( @$params['path'] && !is_numeric($params['path']) && $params['path']{0} != 'm' ) {
        throw new Exception( "path parameter is invalid.  It should begin with m or an integer number.");
    }
    
    $params['cols'] = get_cols( $params );
    $params['path'] = @$params['path'] ?: 'm';
    
    $params['format'] = @$params['format'] ?: 'txt';
    $params['cols'] = @$params['cols'] ?: 'all';
    $params['numderive'] = @$params['numderive'] ?: 10;
    $params['includeroot'] = isset($params['includeroot'] );

    return [$params, $success];
}

/**
 * prints program version text
 */
function print_version() {
    $version = @file_get_contents(  __DIR__ . '/VERSION');
    echo $version ?: 'version unknown' . "\n";
}


/* prints CLI help text
 */
function print_help() {
    
    $levels = mylogger()->get_level_map();
    $allcols = implode(',', wallet_derive::all_cols() );
    $defaultcols = implode(',', wallet_derive::default_cols() );
    
    $loglevels = implode(',', array_values( $levels ));
     
    $buf = <<< END

   hd-wallet-derive.php

   This script derives private keys and public addresses

   Options:

    -g                   go!  ( required )
    
    --key=<key>          xpriv or xpub key
    --mnemonic=<words>   bip39 seed words
                           note: either key or nmemonic is required.
                           
    --mnemonic-pw=<pw>   optionally specify password for mnemonic.
                            
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
function get_cols( $params ) {
    $arg = strip_whitespace( @$params['cols'] ?: null );
    
    $allcols = wallet_derive::all_cols();
    
    if( $arg == 'all' ) {
        $cols = $allcols;
    }
    else if( !$arg ) {
        $cols = wallet_derive::default_cols();
    }
    else {
        $cols = explode( ',', $arg );
        foreach( $cols as $c ) {
            if( !in_array($c, $allcols) ) {
                throw new Exception( "'$c' is not a known report column.", 2 );
            }
        }
    }

    return $cols;
}


/* removes whitespace from a string
 */
function strip_whitespace( $str ) {
    return preg_replace('/\s+/', '', $str);
}
