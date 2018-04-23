<?php

namespace App\Utils;

use App\Utils\MyLogger;
use Exception;
use PDO;
use PDOException;

/***
 * A mysql utility class for seorev scripts.
 */
class MysqlUtil {

    /***
     * Get PDO connection to seorev_stats DB
     */
    static public function get_stats_pdo_connection() {
        return self::get_pdo_connection( DB_STATS_SERVER, DB_STATS_NAME, DB_STATS_USER, DB_STATS_PASSWD );
    }
    
    /***
     * Get PDO connection to seorev_process DB
     */
    static public function get_process_pdo_connection() {
        return self::get_pdo_connection( DB_PROCESS_SERVER, DB_PROCESS_NAME, DB_PROCESS_USER, DB_PROCESS_PASSWD );
    }
    
    /***
     * Get PDO connection to seorev_ui DB
     */
    static public function get_ui_pdo_connection() {
        return self::get_pdo_connection( DB_UI_SERVER, DB_UI_NAME, DB_UI_USER, DB_UI_PASSWD );
    }
    
    /***
     * Perform query and retrieve one or zero results.
     * The result is an object of type stdClass or null.
     * throws exception if query returns any other number of results.
     */
    static public function query_one_or_none( $pdo, $query ) {
        $results = self::query( $pdo, $query );

        if( count( $results) > 1 ) {
            throw new Exception( sprintf( "Got %s results from query. Expected one or none.", count( $results ) ) );
        }
        return @array_pop( $results );
    }

    /***
     * Perform query and retrieves one result row.
     * The result is an object of type stdClass.
     * throws exception if query returns any other number of results, or none.
     */
    static public function query_one( $pdo, $query ) {
        $results = self::query( $pdo, $query );
        if( count( $results) != 1 ) {
            throw new Exception( sprintf( "Got %s results from query. Expected one.", count( $results ) ) );
        }
        return array_pop($results);
    }

    /***
     * Perform query and retrieves one result scalar, or null.
     * The result is a scalar string/int/float.
     * throws exception if query returns any other number of results.
     */
    static public function query_one_scalar( $pdo, $query ) {
        $row = self::query_one( $pdo, $query );
        $row = get_object_vars( $row );
        if( @count( $row ) != 1 ) {
            throw new Exception( "Expected a single column row" );
        }
        return array_pop( $row );
    }    
    
    /***
     * Perform query and retrieves one result scalar, or null.
     * The result is a scalar string/int/float.
     * throws exception if query returns any other number of results.
     */
    static public function query_one_or_none_scalar( $pdo, $query ) {
        $row = self::query_one_or_none( $pdo, $query );
    
        if( $row ) {
            $row = get_object_vars( $row );
            if( @count( $row ) != 1 ) {
                throw new Exception( "Expected a single column row" );
            }
        }
        return @array_pop( $row );
    }

    
    /***
     * Perform query and retrieve results in array.
     * Each result is an object of type stdClass.
     */
    static public function query( $pdo, $query, $type = null ) {
        try {
            mylogger()->log( sprintf( "Executing query: %s\n", $query ), mylogger::debug );
            $tstart = microtime(true);
            $stmt = $pdo->query( $query, PDO::FETCH_CLASS, 'stdClass' );
            $duration = microtime(true) - $tstart;
            $query_was = $duration > 1 ? sprintf( "Query was:\n%s", $query ) : '';
            mylogger()->log( sprintf( "Query took: %s seconds. %s", $duration, $query_was ), mylogger::debug );
            if( $stmt->columnCount() == 0 ) {
               return array();
            }
            if( $type ) {
               return $stmt->fetchAll( $type );
            }
            return $stmt->fetchAll();
        }
        catch (PDOException $e)
        {
            mylogger()->log( sprintf( "\n\nMySQL PDO Error %s: \n\nQuery was:\n  ---> %s\n\n%s:%s\n%s\nTrace:\n%s\n\n", $e->getCode(), $query, $e->getFile(), $e->getLine(), $e->getMessage(), $e->getTraceAsString() ), mylogger::fatalerror );
            throw( $e );
        }
    }
    
    /***
     * Returns a PDO connection object.
     */
    static public function get_pdo_connection($host, $db, $user, $pass) {
        
        $dsn = sprintf( 'mysql:host=%s;dbname=%s', $host, $db );
        
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_TIMEOUT => 86400 * 365,    // 365 days = long time.
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );       
        $conn = new PDO( $dsn, $user, $pass, $options );
        
        return $conn;
    }
    
    /***
     * Escapes values for use in SQL queries.
     */
    static public function pdo_escape( $pdo, $val ) {
    
        if( is_string( $val ) ) {
            $val = $pdo->quote( $val );
        }
        if( $val === null ) {
            $val = 'NULL';
        }
        return $val;    
    }
    
    /***
     * Escapes values in a row for insert/update.
     */
    static public function quote_row_vals( $pdo, $row ) {
        
        $rowvals = array();
        foreach( $row as $val ) {
            $rowvals[] = self::pdo_escape( $pdo, $val );
        }
        
        return $rowvals;
    }
    
    /***
     * Escapes array values and and returns a string ready for use in an IN (...) clause.
     */
    static public function quote_in_clause_vals( $pdo, $vals ) {
        $vals = self::quote_row_vals( $pdo, $vals );
        return implode( ",", $vals );
    }


    /***
     * Obtains a named sql lock.
     * see http://dev.mysql.com/doc/refman/5.1/en/miscellaneous-functions.html#function_get-lock
     */
    static public function get_lock( $pdo, $name, $timeout = PHP_INT_MAX  ) {
        $mask = 'select get_lock(%s, %s)';
        $query = sprintf( $mask,
                          self::pdo_escape($pdo, $name ),
                          self::pdo_escape($pdo, $timeout )
                        );
        return self::query_one_scalar( $pdo, $query );        
    }

    /***
     * Obtains a named sql lock, prefixed with name of current db.
     * returns lockname on success, or return value of get_lock() on error.
     */
    static public function get_db_lock( $pdo, $name, $timeout = PHP_INT_MAX  ) {
return 'foo';
        $mask = "select concat(database(), '.', %1\$s) as lockname, get_lock( concat(database(), '.', %1\$s), %2\$s) as rc";
        $query = sprintf( $mask,
                          self::pdo_escape($pdo, $name ),
                          self::pdo_escape($pdo, $timeout )
                        );
        $row = self::query_one( $pdo, $query );
        return $row->rc == 1 ? $row->lockname : $row->rc;
    }
    
    
    /***
     * Releases a named sql lock.
     * Returns 1 if the lock was released, 0 if the lock was not established
     * by this thread (in which case the lock is not released), and NULL if
     * the named lock did not exist
     */
    static public function release_lock( $pdo, $name ) {
return;
        $mask = 'select release_lock(%s)';
        $query = sprintf( $mask,
                          self::pdo_escape($pdo, $name )
                        );
        return self::query_one_scalar( $pdo, $query );        
    }
    
    /**
     * Checks whether the lock named str is free to use (that is, not locked)
     * Returns 1 if the lock is free (no one is using the lock), 0 if the lock is in use,
     * and NULL if an error occurs.
     */
    static public function is_free_lock( $pdo, $name ) {
        $mask = 'select is_free_lock(%s)';
        $query = sprintf( $mask,
                          self::pdo_escape($pdo, $name )
                        );
        return self::query_one_scalar( $pdo, $query );        
    }
    
    /**
     * Checks whether the lock named str is in use (that is, locked).
     * If so, it returns the connection identifier of the client that
     * holds the lock. Otherwise, it returns NULL. 
     */    
    static public function is_used_lock( $pdo, $name ) {
        $mask = 'select is_used_lock(%s)';
        $query = sprintf( $mask,
                          self::pdo_escape($pdo, $name )
                        );
        return self::query_one_scalar( $pdo, $query );        
    }
    
    /**
     * determines database name of PDO connection.
     */    
    static public function dbname( $pdo ) {
        $query = 'select database()';
        return self::query_one_scalar( $pdo, $query );        
    }
    
    static public function format_results_fixed_width( $results ) {
        
        if( !count( $results ) ) {
            return <<< 'END'
+------------+
| No results |
+------------+ 
END;
        }

        $obj_arr = function ( $t ) {
           return is_object( $t ) ? get_object_vars( $t ) : $t;
        };
        
        $header = array_keys( $obj_arr( $results[0] ) );
        $col_widths = array();

        $calc_row_col_widths = function( &$col_widths, $row ) {
            $idx = 0;
            foreach( $row as $val ) {
                $len = strlen( $val );
                if( $len > @$col_widths[$idx] ) {
                    $col_widths[$idx] = $len;
                }
                $idx ++;
            }
        };
        
        $calc_row_col_widths( $col_widths, $header );
        foreach( $results as $row ) {
            $row = $obj_arr( $row );
            $calc_row_col_widths( $col_widths, $row );
        }

        $print_row = function( $col_widths, $row ) {
            $buf = '|';
            $idx = 0;
            foreach( $row as $val ) {
                $pad_type = is_numeric( $val ) ? STR_PAD_LEFT : STR_PAD_RIGHT;
                $buf .= ' ' . str_pad( $val, $col_widths[$idx], ' ', $pad_type ) . " |";
                $idx ++;
            }
            return $buf . "\n";
        };
        
        $print_divider_row = function( $col_widths ) {
            $buf = '+';
            foreach( $col_widths as $width ) {
                $buf .= '-' . str_pad( '-', $width, '-' ) . "-+";
            }
            $buf .= "\n";
            return $buf;
        };
        
        $buf = $print_divider_row( $col_widths );
        $buf .= $print_row( $col_widths, $header );
        $buf .= $print_divider_row( $col_widths );
        foreach( $results as $row ) {
            $row = $obj_arr( $row );
            $buf .= $print_row( $col_widths, $row );
        }
        $buf .= $print_divider_row( $col_widths );
        
        return $buf;
    }    
}
