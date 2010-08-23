<?php
/**
 * Registry: create global config stuff
 * Propably a bad design pattern.
 */
class Nano_Registry{
    private static $_instance;
    private $_values;

    public static function add( $name, $value ){
        self::getInstance()->addValue( $name, $value );
    }

    public static function get( $name ){
        return self::getInstance()->getValue( $name );
    }

    public static function getInstance(){
        if( null == self::$_instance ){
            self::$_instance = new Nano_Registry();
        }
        return self::$_instance;
    }

    private function __construct(){
        $this->_values = new Nano_Collection();
    }

    private function addValue( $name, $value ){
        $this->_values->$name = $value;
    }

    private function getValue( $name ){
        return $this->_values->$name;
    }
}
