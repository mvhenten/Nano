<?php
/**
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @copyright Copyright (c) 2009 Matthijs van Henten
 */
class Nano_Db{
    private $_adapters = array();
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
        $this->_adapters[$name] = null;

        if( $config instanceof PDO ){
            $adapter = $config;
        }
        else if( is_array( $config ) && isset( $config['dsn'] ) ){
            $config = array_merge( array(
                'username' => null,
                'password' => null ), $config
            );

            $adapter = new PDO( $config['dsn'], $config['username'], $config['password'] );
        }

        $this->_adapters[$name] = $adapter;
    }

    private function _getAdapter( $name ){
        if( key_exists( $name, $this->_adapters ) ){
            return $this->_adapters[$name];
        }
        else{
            throw new Exception( 'Adapater does not exist: ' . $name );
        }
    }
}
