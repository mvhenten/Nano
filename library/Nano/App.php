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
    private $_namespace;
    private $_config;

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

    public function dispatch(){
        foreach( $this->namespace as $ns => $path ){
            $klass = array( $ns, 'View', $this->request->module, $this->request->view );
            $klass = join( '_', array_map('ucfirst', array_filter($klass)));

            if( class_exists($klass) ){
                $view = new $klass( $this->request, $this->config );
                return $view->response()->out();
            }
        }
    }

    public function __get( $name ){
        $name = '_' . $name;

        if( property_exists( $this, $name ) ){
            if( null == $this->$name && ( $method = '_get' . $name ) && method_exists( $this, $method ) ){
                $this->$name = $this->$method();
            }

            return $this->$name;
        }
    }

    private function _get_namespace(){
        if( isset($this->_build_args['namespace']) ){
            return (array) $this->_build_args['namespace'];
        }

        return array();
    }

    private function _get_request(){
        return new Nano_Request( $this->router );
    }

    private function _get_router(){
        $route_settings = array();

        if( isset( $this->_build_args['router'] ) ){
            $route_settings = $this->_build_args['router'];
        }

        return new Nano_App_Router( $route_settings );
    }

    private function _get_config(){
        if( isset($this->_build_args['config'])){
            return $this->_build_args['config'];
        }
        return array();
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
