<?php
namespace WPPluginsDev\Helpers;
use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

/**
 * Debug Helper Class.
 *
 * @since 1.0.0
 */
class Helper_Debug {
	const PROJECT = 'WPPluginsDev';
    const DEFAULT_PATH = '/var/log/php/error.log';
    
    protected static $debug_logger;

	public static function debug( $message ) {
		if( ! self::is_debug_mode() ) {
			return;
		}
		
        if( empty( self::$debug_logger ) ) {

            $log_path = ini_get( 'error_log' );
            if( empty( $log_path ) ) {
                $log_path = self::DEFAULT_PATH;
            }
            
            $project = self::PROJECT;
            if( defined( 'DB_NAME' ) ) {
                $project = DB_NAME;
            }
            
            self::$debug_logger = new Logger( $project );
            $formatter = new LineFormatter(
                null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
                null, // Datetime format
                true, // allowInlineLineBreaks option, default false
                true  // discard empty Square brackets in the end, default false
            );
            // Debug level handler
            $debugHandler = new StreamHandler( $log_path, Logger::DEBUG );
            $debugHandler->setFormatter( $formatter );
            self::$debug_logger->pushHandler( $debugHandler );            
        }
        
        $trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
        $exception = new Exception();
        $caller = array_shift( $trace );
        $exception = $exception->getTrace();
        $callee = array_shift( $exception );
        if ( is_array( $message ) || is_object( $message ) ) {
            $message = print_r( $message, true );
        } 
        
        if( isset( $caller['class'] ) && self::class != $caller['class'] ) {
            $message = sprintf( "%s[%s]: %s", $caller['class'], $callee['line'], $message );
        } 
        else {
            $message = sprintf( "%s[%s]: %s", basename( $caller['file'] ), $callee['line'], $message );
        }
        
        self::$debug_logger->debug( $message );
	}
	
	/**
	 * Logs errors to WordPress debug log.
	 *
	 * The following constants ned to be set in wp-config.php
	 * or elsewhere where turning on and off debugging makes sense.
	 *
	 *     // Essential
	 *     define('WP_DEBUG', true);  
	 *     // Enables logging to /wp-content/debug.log
	 *     define('WP_DEBUG_LOG', true);  
	 *     // Force debug messages in WordPress to be turned off (using logs instead)
	 *     define('WP_DEBUG_DISPLAY', false);  
	 *
	 * @since 1.0.0
	 * @param  mixed $message Array, object or text to output to log.
	 */
	public static function log( $message, $echo_file = false ) {
		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		$exception = new Exception();
		$debug = array_shift( $trace );
		$caller = array_shift( $trace );
		$exception = $exception->getTrace();
		$callee = array_shift( $exception );

		if ( is_array( $message ) || is_object( $message ) ) {
			$class = isset( $caller['class'] ) ? $caller['class'] . '[' . $callee['line'] . '] ' : '';
			if ( $echo_file ) {
				error_log( $class . print_r( $message, true ) . 'In ' . $callee['file'] . ' on line ' . $callee['line'] );	
			} else {
				error_log( $class . print_r( $message, true ) );	
			}
		} else {
			$class = isset( $caller['class'] ) ? $caller['class'] . '[' . $callee['line'] . ']: ' : '';
			if ( $echo_file ) {
				error_log( $class . $message . ' In ' . $callee['file'] . ' on line ' . $callee['line']);					
			} else {
				error_log( $class . $message );					
			}
		}
	}
	
	/**
	 * Print debug stack trace.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $return Flag to return string instead of printing.
	 * @return string|NULL The stack trace. 
	 */
	public static function debug_trace( $return = false ) {
		$traces = debug_backtrace();
		$fields = array(
			'file',
			'line',
			'function',
			'class',
		);
		$log = array( "**************************** Trace start ****************************" );
		foreach( $traces as $i => $trace ) {
			$line = array();
			foreach( $fields as $field ) {
				if( ! empty( $trace[ $field ] ) ) {
					$line[] = "$field: {$trace[ $field ]}";
				}
			}
			$log[] = "  [$i]". implode( '; ', $line );
		}
//		$log = array_reverse( $log, true );
		if( $return ) {
			return implode( "\n", $log);	
		}
		else {
			error_log( implode( "\n", $log) );
		}
	}
	
	/**
	 * Default error handler.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $errno The error number.
	 * @param string $errstr The error description.
	 * @param string $errfile The file where the error ocurred.
	 * @param unknown $errline The file line where the error occurred.
	 * @param unknown $errcontext The context the error occurred.
	 */
	public static function process_error_backtrace( $errno, $errstr, $errfile, $errline, $errcontext = null ) {
		if( ! ( error_reporting() & $errno ) ) {
			return;
		}
		switch( $errno ) {
			case E_WARNING      :
			case E_USER_WARNING :
			case E_STRICT       :
			case E_NOTICE       :
			case E_USER_NOTICE  :
				$type = 'warning';
				$fatal = false;
				break;
			default             :
				$type = 'fatal error';
				$fatal = true;
				break;
		}
		$message = "[$type]: '$errstr' file: $errfile, line: $errline";
		error_log( $message );
		self::debug_trace();
		
		if( $fatal ) {
			exit(1);
		}
	}
	
	/**
	 * Get debug mode status.
	 * 
	 * @since 1.0.0
	 * 
	 * @return boolean
	 */
	public static function is_debug_mode() {
		$debug = false;
		if( ( defined( 'WP_DEBUG' ) && true == WP_DEBUG ) || ( defined( 'VDS_ENV' ) && 'dev' == VDS_ENV  ) ) {
			$debug = true;
		}
		return $debug;
	} 
}

set_error_handler( array( '\\WPPluginsDev\\Helpers\\Helper_Debug', 'process_error_backtrace') );

