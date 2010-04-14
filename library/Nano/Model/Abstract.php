<?php
abstract class Nano_Model_Abstract{
    private $mapper;
    protected function getMapper(){
        if( null == $this->mapper ){
            $name = sprintf( '%s_Mapper', get_class( $this ));
            $this->mapper = new $name();
        }
    }
}
