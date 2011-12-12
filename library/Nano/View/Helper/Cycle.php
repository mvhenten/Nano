<?php
class Nano_View_Helper_Cycle extends Nano_View_Helper{
    private $_index = 0;
    
    private $_currentCycle = null;
    
    function Cycle( $args ){
        $to_cycle = func_get_args();
        
        if( $to_cycle != $this->_currentCycle ){
            $this->_index = 0;
        }
        
        if( $this->_index >= count($to_cycle) ){
            $this->_index = 0;
        }
        
        $this->_currentCycle = $to_cycle;
        $this->_index++;

        return $to_cycle[$this->_index-1];        
    }
}
