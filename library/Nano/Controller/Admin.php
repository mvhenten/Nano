<?php
class Nano_Controller_Admin extends Nano_Controller{
    protected function init(){
        if( !Nano_Admin::hasIdentity() ){
            $this->redirect( '/' );
        }
    }
}
