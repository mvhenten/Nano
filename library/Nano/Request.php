<?php
class Nano_Request{
    private $_request;
    private $_post;
    private $_get;
    private $_router;
    private $_headers;

    public function __construct( Nano_Router $router = null ){
        if( null !== $router ){
            $this->_router = $router;
        }
    }

    public function __get( $name ){
        if( null !== $this->_router && null !== $this->_router->$name ){
            return $this->_router->$name;
        }
        elseif( null !== $this->getPost() && null !== $this->_post->$name ){
            return $this->_post->$name;
        }

        return $this->getRequest()->$name;
    }

    public function getRouter(){
        return $this->_router;
    }

    public function isXmlHttpRequest(){
        if( null == $this->_headers ){
            $this->_headers = apache_request_headers();
        }
        //["X-Requested-With"]=>
        //string(14) "XMLHttpRequest"

        if( isset( $this->_headers['X-Requested-With'] ) ){
            if( $this->_headers['X-Requested-With'] == 'XMLHttpRequest' ){
                return true;
            }
        }
        return false;
    }

    public function isPost(){
        if( count( $_POST ) > 0 ){
            return true;
        }
        return false;
    }

    public function getPost(){
        if( $this->isPost() && null == $this->_post ){
            $this->_post = new Nano_Collection( $_POST );
        }

        return $this->_post;
    }

    public function getRequest(){
        if( null == $this->_request ){
            $this->_request = new Nano_Collection( $_REQUEST );
        }
        return $this->_request;
    }

    public function getRequestUri(){
        return $this->_router->getRequestUri();
    }
}
