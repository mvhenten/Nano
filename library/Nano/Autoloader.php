<?php
/**
 *@file Autoloader.php
 *@group libnano
 */
/**
 * @class Nano_Autoloader
 *
 * @description Autoloader is the default autoload implementation for libnano
 * @author Matthijs van Henten <nano@ischen.nl>
 * @copyright Copyright (c) 2009, Matthijs van Henten
 * @license GPL 2.0
 * @todo implement as a singleton
 */
class Nano_Autoloader{
    /**
     * Wraps around spl_autoload_register
     * If no parameters are given, the default ( itself )
     * autoloader is registered.
     *
     * @author Matthijs van Henten <nano@ischen.nl>
     * @param function $function Function format similar to call_user_func_array()
     */
    static function register( $function = null ){
        if( null === $function ){
            $function = array( 'self', 'load' );
        }

        spl_autoload_register( $function );
    }

    /**
     * Basic auloader. loads classes from libnano
     */
    static function load( $name ){
        static $blacklist = array();

        $path = explode( '_', $name );//array_slice( explode( '_', $name ), 1);
        $dir  = rtrim( str_replace( 'Nano/Autoloader.php', '', __FILE__ ), '/');

        if( count( $path ) > 1 ){
            $path = join( '/', $path );
        }
        else if( count( $path ) > 0 ){
            $path = join( '/', array($path[0],$path[0]) );
        }

        $path = $dir . '/' . $path . '.php';

        if( ! in_array( $name, $blacklist ) && file_exists( $path ) ){
            $blacklist[] = $name;
            include( $path );
        }
    }
}
