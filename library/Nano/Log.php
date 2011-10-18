<?php
if( ! defined('PICO_ERROR_LOG') ){
    define( 'PICO_ERROR_LOG',  '/tmp/pico-error.log' );
}
class Nano_Log {
    public static function error( $str, $key = 'E' ){
        $date = date('d-M-Y H:s:i');

        ob_start();
        print_r($str);

        $lines = explode("\n",  ob_get_clean() );

        foreach( $lines as $value ){
            error_log( "[$date] $key: $value\n", 3, PICO_ERROR_LOG );
        }
    }
}
