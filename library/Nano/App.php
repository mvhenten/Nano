<?php
/**
 * @file Nano/App.php
 *
 * Minmal base class to subclass a bootstrap from
 *
 * Copyright (C) <2011>  <Matthijs van Henten>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    Nano
 * @author     Matthijs van Henten <matthijs(a)ischen.nl>
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 */

require_once( dirname(__FILE__) . '/Autoloader.php');
Nano_Autoloader::register();

/**
 * <code>
 * Nano_App::Bootstrap(array(
 *      namespace => array( 'MyApp' => dirname(__FILE__) . 'lib' ),
 *      config => array()
 *      nano_db => array(
 *          dsn       =>
 *          username  =>
 *          password  =>
 *      ),
 *      router   => array(
 *          'pattern/to/match'  => controller
 *          '*'                 => DefaultController
 *      )
 * ));
 * </code>
 */
class Nano_App {
    private $_build_args = array();

    private $_request;
    private $_router;
    private $_config;
    private $_plugins;
    private $_response;

    public static function Bootstrap( $args ){
        return new Nano_App( $args );
    }

    public function __construct( $build_args ){
        $this->_build_args = $build_args;

        if( isset( $build_args['nano_db'] ) ){
            Nano_Db::setAdapter( $build_args['nano_db'] );
        }

        $this->_registerNamespace();
    }

    public function __get( $name ){
        if( method_exists( $this, $name ) ){
            return $this->$name();
        }
    }

    public function dispatch(){
        $this->plugins->hook( 'start', $this, array('request' => $this->request) );

        list( $handler, $matches, $pattern ) = $this->router->getRoute( $this->request->url );

        if( !$handler ){
            header("Status: 404 Not Found", true, 404 );
            exit();
        }

        $handler_object = new $handler( $this->request, $this->_build_args );
        $this->plugins->hook( 'end', $handler_object, array('request' => $this->request) );

        $handler_object->response()->out();
    }

    public function request(){
        return $this->_lazy_build('request', array(
            'isa' => 'Nano_App_Request'
        ));
    }

    public function router(){
        $route_settings = array();

        return $this->_lazy_build('router', array(
            'isa'        => 'Nano_App_Router',
            'build_args' => isset($this->_build_args['router'])
                ? $this->_build_args['router'] : array()
        ));
    }

    public function plugins(){
        $route_settings = array();

        return $this->_lazy_build('plugins', array(
            'isa'        => 'Nano_App_Plugin_Helper',
            'build_args' => isset($this->_build_args['plugins'])
                ? $this->_build_args['plugins'] : array()
        ));
    }

    private function response(){
        return $this->_lazy_build('request', array(
            'isa' => 'Nano_Response'
        ));
    }

    private function config(){
        if( isset($this->_build_args['config'])){
            return $this->_build_args['config'];
        }
        return array();
    }


    private function _lazy_build( $name, array $args, array $build_args = array() ){
        $property = "_$name";

        if( property_exists( $this, $property ) && null === $this->$property ){
            $build_args = isset($args['build_args']) ? $args['build_args'] : null;
            $klass      = $args['isa'];

            if( $build_args ){
                $instance = new $klass( $build_args );
            }
            else{
                $instance = new $klass;
            }

            $this->$property = $instance;

        }

        return $this->$property;
    }

    private function _registerNamespace(){
        if( !isset($this->_build_args['namespace']) ){
            return;
        }

        $namespaces = (array) $this->_build_args['namespace'];

        foreach( $namespaces as $ns => $path ){
            Nano_Autoloader::registerNamespace($ns , $path );
        }
    }


}
