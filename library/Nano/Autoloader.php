<?php
if( ! defined( 'APPLICATION_PATH' ) ){
    define('APPLICATION_PATH', dirname(__FILE__) );
    define( 'APPLICATION_ROOT', dirname(APPLICATION_PATH));
}

class Nano_Autoloader{
    private static $instance;
    private $namespaces = array();

    private function __construct(){
    }

    public static function register(){
        spl_autoload_register( 'Nano_Autoloader::autoLoad' );
        Nano_Autoloader::registerNamespace( 'Nano', dirname(__FILE__) );
    }

    public static function getInstance(){
        if( null === self::$instance ){
            self::$instance = new Nano_AutoLoader();
        }

        return self::$instance;
    }

    public static function autoLoad( $name ){
        self::getInstance()->load( $name );
    }

    public static function getNamespaces(){
        return self::getInstance()->namespaces;
    }

    public static function registerNamespace( $name, $path ){
        self::getInstance()->addNamespace( $name, $path );
    }

    private function addNamespace( $name, $path ){
        $this->namespaces[$name] = $path;
    }

    private function load( $name ){
        $pieces = explode( '_', $name );

        foreach( $this->namespaces as $key => $value ){
            if( strpos( $name, $key ) === 0 && strlen($name) > $key ){
                $file = array_filter( explode('_',substr( $name, strlen($key) )) );
                $path = sprintf( '%s/%s.php', $value, join( '/', $file ));
                return $this->includePath( $path );
            }
        }

        $path = sprintf( '%s/%s.php', APPLICATION_ROOT, join( '/', $pieces ));
        return $this->includePath( $path, false );
    }

    private function includePath( $path, $fail = false ){
        if( file_exists( $path ) ){
            require_once( $path );
            $fail = false;
        }
        else if( ($lower = strtolower($path)) && file_exists( $lower) ){
            require_once( $lower );
            $fail = false;
        }

        if( $fail ){
            throw new Exception( sprintf( 'File does not exist "%s"', $path ));
        }
        return $fail;
    }
}