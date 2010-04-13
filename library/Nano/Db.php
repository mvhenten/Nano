<?php
/**
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @copyright Copyright (c) 2009 Matthijs van Henten
 */
class Nano_Db{
    private $_adapters;
    private static $_instance;

    public static function setAdapter( $config, $name = 'default' ){
        self::getInstance()->_setAdapter( $config, $name );
    }

    public static function getAdapter( $name = 'default' ){
        return self::getInstance()->_getAdapter( $name );
    }

    private static function getInstance(){
        if( null === self::$_instance ){
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    private function _setAdapter( $config, $name ){
        if( is_array( $config ) ){
            $config = (object) $config;
        }
        if( !is_object( $config ) ){
            throw new Exception( 'Invalid arguments passed: config, ', $config );
        }

        if( ! isset( $config->dsn ) ){
            throw new Exception( 'DB config: DSN is not set!');
        }

        if( stripos( 'mysql', $config->dsn ) == 0 ){
            $adapter = new Nano_Db_Adapter_Mysql( $config );
        }
        else{
            throw new Exception( sprintf( '@TODO: implement %s', $config->dsn ) );
        }

        $this->_adapters[$name] = $adapter;
    }

    private function _getAdapter( $name ){
        if( key_exists( $name, $this->_adapters ) ){
            return $this->_adapters[$name];
        }
    }
}
