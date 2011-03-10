<?php
/**
 * @todo remove Nano_Colleciton dependency!
 */
class Nano_Router extends Nano_Collection{
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

    function parseRoute( $route ){
        $match = preg_match_all( '/\/:(\w+)/', $route['route'], $matches );

        if( intval($match) > 0 ){
            //echo count($matches[1]);
            $replace = array_fill( 0, count($matches[1]), '/(\w+)');
            $pattern = str_replace( $matches[0], $replace, $route['route'] );
            $pattern = str_replace( '/', '\/', $pattern );
        }

        return array( $match, $matches, $pattern );
    }

    function matchRoute( $uri, $route ){
        $uri = addslashes(rtrim( $uri, '/' ));

        $pattern = preg_replace('/:\w+/', '(\w+)', $route['route']);
        preg_match_all('/:(\w+)/', $route['route'], $keys );

        $keys = isset($keys[1])?$keys[1]:array();
        $pattern = explode('/', trim($pattern, '/'));

        // each uri component is optional until matched
        do{
            //print('/'.join('\/', $pattern).'/') . "\n";
            preg_match_all( '/'.join('\/', $pattern).'/', $uri, $matches );

            if( ! empty($matches[0] ) ){
                if( !empty($matches[1] ) && (count($keys) == count($matches[1])) ){
                    $route['defaults'] = array_combine( $keys, $matches[1] ) + $route['defaults'];
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

        return (array) $match;
    }
}
