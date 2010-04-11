<?php
class Nano_Admin{
    private static $_instance;
    private static $_identity;

    /**
     * singleton constructor is hidden
     */
    private function __construct(){/*singleton*/}

    /**
     * Return current instance of Nano_Admin
     * @return Nano_Admin $admin
     */
    public static function getInstance(){
        if( null == self::$_instance ){
            self::$_instance = new Nano_Admin();
        }

        return self::$_instance;
    }

    /**
     * user logged on
     * @return bool $id
     */
    public static function hasIdentity(){
        if( null !== self::getIdentity() ){
            return true;
        }
        return false;
    }

    /**
     * retrieves id from session and stores/returns it
     */
    public static function getIdentity(){
        if( null == self::$_identity ){
            self::$_identity = Nano_Session::getInstance()->Nano_admin;
        }
        return self::$_identity;
    }

    /**
     * unset session admin
     */
    public static function destroyIdentity(){
        Nano_Session::getInstance()->Nano_admin = Null;
    }

    /**
     * set session admin
    */
    public static function setIdentity( $values ){
        self::$_identity = $values;
        Nano_Session::getInstance()->Nano_admin = $values;
    }

}
