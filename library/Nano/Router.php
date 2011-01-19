<?php
/**
 * @todo remove Nano_Colleciton dependency!
 */
class Nano_Router extends Nano_Collection{
    public function __construct( $routes ){
        parent::__construct( $this->getRoute( $routes ) );
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
        $uri = rtrim( $uri, '/' );

        //@todo cache these calculations so they don't have to be done for plugins
        list( $match, $matches, $pattern ) = $this->parseRoute( $route );

        if( intval($match) > 0 ){
            $match = preg_match( "/^$pattern/", $uri, $matches2 );
            if( $match > 0 ){
                $rest   = explode( '/', trim( $uri ) );
                $offset = count( explode( '/', trim( $route['route'] )));
                $rest   = array_slice( $rest, $offset, -1 );
                $chunks = array_chunk( $rest, 2 );

                $route_match = array();

                foreach( $chunks as $chunk ){
                    list( $key, $value ) = array_pad($chunk, 2, True);
                    $route_match["$key"] = $value;
                }

                array_shift($matches2);
                $keys = $matches[1];

                $route_match = array_merge( array_combine( $keys, $matches2 ), $route_match);
                $route_match = array_merge( $route['defaults'], $route_match );

                return $route_match;
            }
        }
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

        return $match;
    }
}
