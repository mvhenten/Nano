<?php
abstract class Nano_View_Helper{
    private $_view = null;

    public function __construct( $view ){
        $this->_view = $view;
    }

    protected function getView(){
        return $this->_view;
    }

}
