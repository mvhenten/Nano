<?php
// provides acess to the global $_SESSION array as an Object
class Nano_Session implements Iterator, ArrayAccess, Countable{
    private $position = 0;
    private $session;

    public function __construct( $sessionId = null ) {
        if( ! isset( $_SESSION ) ){
            session_start( $sessionId );
        }
    }

    public function __get( $name ){
        return $this->offsetGet( $name );
    }

    public function __set( $name, $value ){
        $this->offsetSet( $name, $value );
    }

    public function __unset( $name ){
        $this->offsetUnset( $name );
    }

    public function __isset( $name ){
        return $this->offsetExists( $name );
    }

    public function destroy(){
        session_destroy();
    }

    public function getId(){
        return session_id();
    }

    /** Iterator **/

    public function rewind() {
        reset( $_SESSION );
    }

    public function current() {
        return current( $_SESSION );
    }

    public function key() {
        return key( $_SESSION );
    }

    public function next() {
        return next( $_SESSION );
    }

    public function valid() {
        return current( $_SESSION );
    }

    /** ArrayAccess **/

    public function offsetExists( $key ){
        return (bool) isset( $_SESSION[$key] );
    }

    public function offsetGet( $key ){
        if( $this->offsetExists( $key ) ){
            return $_SESSION[$key];
        }
    }

    public function offsetSet( $key, $value ){
        $_SESSION[$key] = $value;
    }

    public function offsetUnset( $key ){
        unset( $_SESSION[$key] );
    }

    private function getSession(){
        return $_SESSION;
    }

    /** countable **/

    public function count(){
        return count( $_SESSION );
    }
}
