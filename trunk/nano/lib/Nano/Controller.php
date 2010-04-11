<?php
abstract class Nano_Controller{
    private $request;
    private $view;

    protected function initialize(){}
    protected function postDispatch(){}
    protected function preDispatch(){}


    public final function __construct( Nano_Request $request = null ){
        if( null !== $request ){
            $this->request = $request;
        }

        $this->initialize();
        $this->dispatch( $this->getRequest()->action );
    }

    public final function __get( $name ){
        $method = 'get' . ucfirst($name);

        if( method_exists( $this, $method ) ){
            return $this->$method();
        }
    }

    public final function dispatch( $action ){
        $this->preDispatch();

        $method = strtolower( $action ) . 'Action';

        if( method_exists( $this, $method ) ){
            call_user_func( array( $this, $method ) );
            $this->postDispatch();
        }
    }

    public final function getRequest(){
        if( null == $this->request ){
            $this->request = new Nano_Request();
        }

        return $this->request;
    }

    public final function getView(){
        if( null == $this->view ){
            $this->view = new Nano_Template();
        }

        return $this->view;
    }

    public function redirect( $path, $httpCode = '303' ){
        header( sprintf( 'Location: %s', $path ), true, $httpCode );
        exit;
    }
}
