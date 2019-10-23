<?php

namespace App\Utils;

/***
 * A helper function to access single instance of logger class from anywhere.
 * usage: mylogger()->log( "log message", mymylogger::info );
 */

use Exception;

/***
 * A logging class.  Use it!
 */
class MyLogger {

    /***
     * log level constants.
     */
    const debug = 0;
    const info = 1;
    const specialinfo = 2;
    const warning = 3;
    const exception = 4;
    const fatalerror = 5;
        
    public $log_level;
    public $echo_log;
    protected $log_file;
    
    public $admin_email;
    public $admin_email_encrypt;
    
    static protected $level_map = array( self::debug => 'debug',
                                         self::info => 'info',
                                         self::specialinfo => 'specialinfo',
                                         self::warning => 'warning',
                                         self::exception => 'exception',
                                         self::fatalerror => 'fatalerror',
                                        );

    /***
     * constructor
     */
    public function __construct( $log_file = null, $echo_log = true, $log_level = null ) {
        $this->log_file = $log_file;
        $this->log_level = $log_level;
        $this->echo_log = $echo_log;
        
        if( defined('ALERTS_MAIL_TO')  ) {
            $this->admin_email = ALERTS_MAIL_TO;
        }
        else {
            // echo "WARNING: ALERTS_MAIL_TO not defined in config.  alerts will not be sent.\n";
        }

        if( $log_level === null && defined( 'MYLOGGER_LOG_LEVEL' ) ) {
            $log_level = MYLOGGER_LOG_LEVEL;
        }

        // default to highest level of logging.
        $this->log_level = $log_level ?: self::debug;
    }


    public static function getInstance()
    {
        static $l = null;

        if(!$l) {
            $l = new mylogger();
        }
        return $l;
    }

    
    /***
     * set the log file to write to.
     */
    public function set_log_file( $log_file ) {
        $this->log_file = $log_file;
    }

    /***
     * set the log level. must be one of the loglevel constants.
     * Any log messages less than this level will not be written.
     */    
    public function set_log_level( $level ) {
        if( !isset( self::$level_map[$level] ) ) {
            throw new Exception( "Unknown log level: $level" );
        }
        $this->log_level = $level;
    }    

    /***
     * set the log level by name. must be one of:
     *   debug, info, specialinfo, warning, exception, fatalerror
     * Any log messages less than this level will not be written.
     */    
    public function set_log_level_by_name( $level_name ) {
        foreach( self::$level_map as $level => $name ) {
            if( $name == $level_name ) {
                $this->set_log_level( $level );
                return true;
            }
        }
        throw new Exception( "Unknown log level: $level_name ");
    }    

    /***
     * gets path to the log file, or null.
     */        
    public function get_log_file() {
        return $this->log_file;
    }

    /***
     * gets the log level map.
     */        
    static public function get_level_map() {
        return self::$level_map;
    }


    /***
     * gets the name corresponding to a given log level, or null.
     */        
    static public function get_level_name( $log_level ) {
        return @self::$level_map[$log_level];
    }
 
    /***
     * formats exception as a string.  also includes sub-exceptions.
     */        
    static public function exception_to_string( Exception $e ) {
        
        // We want to display the exceptions chronologically, with the oldest first.
        // so we must reverse the order.
        $exceptions = array();
        do {
            $exceptions[] = $e;
        } while( ( $e = $e->getPrevious() ) );
        $exceptions = array_reverse( $exceptions );

        $prev_cnt = count( $exceptions ) - 1;
        $buf = $prev_cnt > 0 ? "Caught exception with $prev_cnt previous exceptions. Printing oldest first.\n" : '';
        
        $count = 1;
        foreach( $exceptions as $e ) {        
            $mask = "#%s: %s. code: %s. %s\n\n%s : %s\n\nStack Trace:\n%s\n";
            $buf .= sprintf( $mask,
                             $count++,
                             get_class($e),
                             $e->getCode(),
                             $e->getMessage(),
                             $e->getFile(),
                             $e->getLine(),
                             $e->getTraceAsString()
                            );
        }
        
        return $buf;
    }

    /***
     * Logs an exception.
     */        
    public function log_exception( Exception $e, $log_level = mylogger::exception ) {
        $msg = self::exception_to_string( $e );
        $this->log( $msg, $log_level );
        return $msg;
    }

    /***
     * Generates/formats a log message.
     * $log_level specifies the type/importance of the message.
     * $last_log will adding timing info since the last call.
     */            
    public function format_log_msg( $msg, $log_level, $last_log = true ) {
        $time_buf = '';        
        if( $last_log ) {
            $time = microtime(true);
            $duration = @$this->_last_time ? $time - $this->_last_time : null;
            $this->_last_time = $time;
            $time_buf = $duration ? sprintf( " [lastlog: %f secs] ", $duration) : '';
        }
        
        $line = sprintf( "%s%s [pid: %s] [%s] -- %s\n", date('c'), $time_buf, getmypid(), @self::$level_map[$log_level], $msg );
        return $line;
    }
    
    /***
     * Logs a message.
     * $log_level specifies the type/importance of the message.
     */            
    public function log( $msg, $log_level ) {

        if( $log_level < $this->log_level ) {
            return;
        }
        $line = $this->format_log_msg( $msg, $log_level, $last_log = true );
        
        if( $this->log_file ) {
            $fh = @fopen( $this->log_file, 'a' );
            if( !$fh ) {
                throw new Exception( sprintf( "Unable to open log file %s", $this->log_file ) );
            }
                
            fwrite( $fh, $line);
            fclose( $fh );
        }
        
        if( $this->echo_log ) {
            echo $line;
            fflush(STDOUT);
        }
    }
    
    public function mail_admin_alert( $msg, $subject = null, $level = self::warning ) {
        
        if( !$subject ) {
            $subject = 'Alert from script';
        }
        
        $subject = sprintf( '[%s] [%s] %s [%s] [pid: %s]',
                            gethostname(),
                            self::get_level_name( $level ),
                            $subject,
                            basename($_SERVER['PHP_SELF']),
                            getmypid()
                           );
        return $this->mail_administrator( $subject, $msg );
    }

    /***
     * Sends mail to the system administrator.
     */        
    public function mail_administrator( $subject, $msg, $email_to = null ) {
        
        $address = $email_to ?: $this->admin_email;
        $encrypt = $this->admin_email_encrypt;
        
        if( !$address ) {
            echo "WARNING: ALERTS_MAIL_TO not defined in config.  alert not sent with subject: $subject\n";
            return;
        }
        
        $headers = // 'From: webmaster@example.com' . "\r\n" .
                   'Reply-To: noreply@searchrev.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        
        if( $encrypt ) {
            $plaintext = "Subject: $subject\n\n$msg";
            $subject = ".......";
            $msg = $cryptext = $this->_gpg_encrypt_to_address( $address, $plaintext );
        }
        
        $rc = mail( $address, $subject, $msg, $headers );
        if( $rc ) {        
            $this->log( "Sent mail to '$address' with subject '$subject'", self::info );
        }
        else {
            $this->log( "mail() returned false (unsent) when sending mail to '$address' with subject '$subject'", self::warning );
        }
    }

    /***
     * Encrypts mail to a GPG address if key available.
     */            
    protected function _gpg_encrypt_to_address( $address, $plaintext ) {
        /*
            The not-so-obvious arguments...
            --armor : Output in ASCII-armored format
            --batch : Do not prompt for warnings or validations
            --always-trust : Help smooth the auto-send process
            --no-secmem-warning : Supreess insecure memory warnings
        */
    
        $safe_message = escapeshellarg($plaintext);
        $safe_email_address = escapeshellarg($address);
    
        $command = "printf '%b' $safe_message | gpg  --encrypt --armor " .
                   "--batch --always-trust --no-secmem-warning --recipient $safe_email_address 2> /dev/null";

        $rc = @exec ( $command, $output, $status );
    
        $output = implode("\n", $output);
    
        $success = ( $status === 0 ) ? true : false;
        if (!$success) {
            if(strstr($output, "public key not found")) {
                throw new Exception( "GPG encryption failed because public key was not found in keyring for address $address\n  Command was:\n$command\n  Output was:\n$output", $code );
            }
            throw new Exception( "GPG encryption to $address failed.\n  Command was:\n$command\n  Output was:\n$output" );
        }
        return $output;
    }

    
}
