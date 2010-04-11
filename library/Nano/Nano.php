<?php
/**
 * Class Nano is a static class that will implement some utility functions for working with Nano
 */
class Nano{
    static private $values;

    /**
     * Constructor is kept private to prevent instantiation
     */
    private function __construct(){}
    private function __clone(){}

    /**
     * Factory function: instantiates and stores a named copy of Nano_Db
     *
     * @todo implement proper factory methods inside a singleton
     *
     * @param string $name (Optional) identifier for the database instance or defaultdb
     * @param array $values (Optional) arguments for Nano_Db::__construct()
     *
     * @return Nano_Db $name Nano_Db stored under $name
     */
    static function Db( $name = 'defaultdb', $values = null ){
        if( null == self::$values ){
            self::$values = array();
        }
        if( !isset( self::$values['db'] ) ){
            self::$values['db'] = array();
        }
        if( null !== $values ){
            $db = new Nano_Db( $values );
            self::$values['db'][$name] = $db;
        }
        if( isset( self::$values['db'][$name] ) ){
            return self::$values['db'][$name];
        }
    }
}
