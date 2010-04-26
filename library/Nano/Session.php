<?php
/**
 * @todo this is a stub implementation that sets/gets session array
 */
class Nano_Session{
    private static $_instance;
    private $_identity;

    /**
     * singleton constructor is hidden
     */
    private function __construct(){
        session_start();
    }

    public static function start(){
        return self::getInstance();
    }

    public static function destroy(){
        //@todo this is a stub
        session_destory();
    }

    public static function session(){
        return self::getInstance();
    }

    /**
     * Return current instance
     * @return Nano_Session $session
     */
    public static function getInstance(){
        if( null == self::$_instance ){
            self::$_instance = new Nano_Session();
        }

        return self::$_instance;
    }

    public function __get( $name ){
        if( isset($_SESSION) && key_exists( $name, $_SESSION ) ){
            return $_SESSION[$name];
        }
    }

    public function __set( $name, $value ){
        if( isset( $_SESSION ) ){
            $_SESSION[$name] = $value;
        }
        return $this;
    }


}
