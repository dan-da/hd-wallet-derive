<?php
/**
 * Created by PhpStorm.
 * User: massi
 * Date: 18-4-20
 * Time: 下午6:47
 */

namespace App\Utils;

use texttable;
use texttable_markdown;

/* A class that generates wallet-discovery reports in various formats.
 */
class WalletDeriveReport
{

    /* prints out single report in one of several possible formats,
     * or multiple reports, one for each possible format.
     */
    static public function printResults($params, $results, $fixedCols=false)
    {
        $format = $params['format'];
        $outfile = @$params['outfile'];

        $summary = [];  // placeholder

        // remove columns not in report and change column order.
        $report_cols = $params['cols'];
        
        if(!$fixedCols) {

            foreach( $results as &$r ) {
                $tmp = $r;
                $r = [];
                foreach( $report_cols as $colname ) {
                    $r[$colname] = $tmp[$colname];
                }
            }
        }

        if( $outfile && $format == 'all' ) {
            $formats = array( 'txt', 'md', 'csv', 'json', 'jsonpretty', 'html', 'list' );

            foreach( $formats as $format ) {

                $outfile = sprintf( '%s/%s.%s',
                    pathinfo($outfile, PATHINFO_DIRNAME),
                    pathinfo($outfile, PATHINFO_FILENAME),
                    $format );

                self::print_results_worker( $summary, $results, $outfile, $format );
            }
        }
        else {
            self::print_results_worker( $summary, $results, $outfile, $format );
        }
    }

    /* prints out single report in specified format, either to stdout or file.
     */
    static public function print_results_worker( $summary, $results, $outfile, $format ) {

        $fname = $outfile ?: 'php://stdout';
        $fh = fopen( $fname, 'w' );

        switch( $format ) {
            case 'txt':        self::write_results_fixed_width( $fh, $results, $summary ); break;
            case 'md':         self::write_results_markdown( $fh, $results, $summary ); break;
            case 'list':       self::write_results_list( $fh, $results, $summary );    break;
            case 'csv':        self::write_results_csv( $fh, $results );         break;
            case 'json':       self::write_results_json( $fh, $results );        break;
            case 'html':       self::write_results_html( $fh, $results );        break;
            case 'jsonpretty': self::write_results_jsonpretty( $fh, $results );  break;
        }

        fclose( $fh );

//        if( $outfile ) {
//            mylogger()->log( "Report was written to $fname", mylogger::specialinfo );
//        }
    }

    /* writes out results in json (raw) format
     */
    static public function write_results_json( $fh, $results ) {
        if(count($results) == 1) {
            $results = $results[0];
        }
        fwrite( $fh, json_encode( $results ) );
    }

    /* writes out results in jsonpretty format
     */
    static public function write_results_jsonpretty( $fh, $results ) {
        if(count($results) == 1) {
            $results = $results[0];
        }
        fwrite( $fh, json_encode( $results,  JSON_PRETTY_PRINT ) . "\n" );
    }

    /* writes out results in csv format
     */
    static public function write_results_csv( $fh, $results ) {
        if( @$results[0] ) {
            fputcsv( $fh, array_keys( $results[0] ) );
        }

        foreach( $results as $row ) {
            fputcsv( $fh, $row );
        }
    }

    /* writes out results in html format
     */
    static public function write_results_html( $fh, $results ) {
        $html = '';
        $data = [];

        // make our own array to avoid modifying the original.
        foreach( $results as $row ) {
            $myrow = $row;
            if( isset( $myrow['addr'] ) ) {
                $addr_url = sprintf( 'http://blockchain.info/address/%s', $myrow['addr'] );
                $myrow['addr'] = sprintf( '<a href="%s">%s</a>', $addr_url, $myrow['addr'] );
            }
            $data[] = $myrow;
        }

        if( @$data[0] ) {
            $header = array_keys( $data[0] );
        }
        else {
            // bail.
            return $html;
        }

        $table = new HtmlTable();
        $table->header_attrs = array();
        $table->table_attrs = array( 'class' => 'wallet-derive bordered' );
        $html .= $table->table_with_header( $data, $header );

        fwrite( $fh, $html );
    }

    /* writes out results as a plain text table.  similar to mysql console results.
     */
    static protected function write_results_fixed_width( $fh, $results, $summary ) {

        $buf = texttable::table( $results );
        fwrite( $fh, $buf );

        fwrite( $fh, "\n" );
    }

    /* writes out results as a markdown table.
     */
    static protected function write_results_markdown( $fh, $results, $summary ) {

        $buf = texttable_markdown::table( $results );
        fwrite( $fh, $buf );

        fwrite( $fh, "\n" );
    }
    
    
    /* writes out results as a plain text list of addresses. single column only.
     */
    static protected function write_results_list( $fh, $results, $summary ) {

        foreach( $results as $info ) {
            $firstcol = array_shift( $info );
            fprintf( $fh, "%s\n", $firstcol );
        }

        fwrite( $fh, "\n" );
    }

}
