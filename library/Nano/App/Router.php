<?php
/**
 * @todo remove Nano_Colleciton dependency!
 */
class Nano_App_Router extends Nano_Collection{
    public function __construct( $routes ){
        parent::__construct( (array) $this->getRoute( $routes ) );
    }

    public function getRequestUri(){
        return $_SERVER['REQUEST_URI'];
    }

    private function getRequestValues( $replace ){
        $url = $this->getRequestUri();
        $url = str_replace( $replace, '', $url );
        $url = explode('?', $url );
        return array_filter(explode( '/', array_shift( $url )));
    }

    function matchRoute( $uri, $route ){
        $uri = addslashes(trim( $uri, '/' ));

        //[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*
        $pattern = preg_replace('/:\w+/', '([A-z0-9+\$_-]+)', $route['route']);
        preg_match_all('/:(\w+)/', $route['route'], $keys );

        $keys = isset($keys[1])?$keys[1]:array();
        $pattern = explode('/', trim($pattern, '/'));

        // each uri component is optional until matched

        do{
            preg_match_all( '/^'.join('\/', $pattern).'/', $uri, $matches, PREG_SET_ORDER );

            if( ! empty($matches[0] ) ){
                array_shift($matches[0]);
                if( !empty($matches[0]) && count($keys) == count($matches[0]) ){
                    $route['defaults'] = array_combine( $keys, $matches[0] ) + $route['defaults'];
                }
                return $route['defaults'];
            }

            array_pop($pattern);
            array_pop($keys);

        }
        while($pattern);
    }

    private function getRoute( $routes ){
        $url = $this->getRequestUri();
        $match = array();

        foreach( $routes as $route ){
            $match = $this->matchRoute( $url, (array) $route );

            if( null !== $match ){
                return $match;
            }
        }

        return $route['defaults'];
    }
}
