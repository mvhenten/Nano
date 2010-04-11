<?php
// global storage
class Nano_Config{
    private static $instance;

    private $storage;
    private $config;

    private static function get( $name, $namespace = 'default' ){
        return self::getInstance()->$name;
    }

    private static function getInstance(){
        if( null === self::$instance ){
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __get( $name ){
        return $this->getStorage()->$name;
    }

    private function __set( $name, $value ){
        $this->getStorage()->$name = $value;
    }

    private function getStorage(){
        if( null == $this->storage ){
            $this->storage = new Nano_Collection();
        }

        return $this->storage;
    }

    private function setStorage( $storage ){
        $this->storage = $storage;
    }
}
