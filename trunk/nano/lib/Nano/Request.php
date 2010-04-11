<?php
class Nano_Request{
    private $route;
    private $post;
    private $request;

    public function __get( $name ){
        if( null !== $this->getRoute()->$name ){
            return $this->getRoute()->$name;
        }
        else if( $this->isPost() ){
            return $this->getPost()->$name;
        }

        return $this->getRequest()->$name;
    }

    /**
     * Returns true if there are post variables set
     *
     * @return bool $isPost
     */
    public function isPost(){
        if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
            return true;
        }

        return false;
    }

    public function getRoute(){
        if( null == $this->route ){
            $route = Nano_Router::getRoute();
            $this->route = new Nano_Collection( $route );
        }

        return $this->route;
    }

    public function getPost(){
        if( null == $this->post ){
            $this->post = new Nano_Collection( $_POST );
        }

        return $this->post;
    }

    public function getRequest(){
        if( null == $this->request ){
            $this->request = new Nano_Collection( $_REQUEST );
        }

        return $this->request;
    }
}
