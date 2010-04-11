<?php
class Nano_Router{
    private static $instance;

    private $routers;
    private $route;

    private function __construct(){}
    private function __clone(){}

    public function getInstance(){
        if( null === self::$instance ){
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function addRouter( $name, $route ){
        self::getInstance()->pushRouter( $name, $route );
    }

    public function getRoute(){
        return self::getInstance()->makeRoute();
    }

    private function pushRouter( $name, $route ){
        if( null == $this->routers ){
            $this->routers = array();
        }
        $this->routers[$name] = $route;
    }

    private function makeRoute(){
        if( null == $this->route ){
            $template = $this->getRouter();
            $route    = array();


			$script_url = isset( $_SERVER['SCRIPT_URL'] ) ? $_SERVER['SCRIPT_URL'] : $_SERVER['REQUEST_URI'];

            foreach( array_filter( explode( '/', $script_url)) as $value ){
                $key = array_shift( array_keys( $template ) );

                if( null === $key ){
                    $template[$value] = null;
                    continue;
                }

                $route[$key] = $value;
                array_shift($template);
            }

            $route = array_merge( $route, $template );
            $this->route = $route;
        }
        return $this->route;
    }

    // dummy implementation. this only allows for one router to be set!
    private function getRouter(){
        if( is_array( $this->routers ) ){
            return array_shift( $this->routers );
        }
        return array();
    }
}
