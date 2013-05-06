<?php
/**
 * library/Nano/App/Request.php
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
 *
 * @category   Nano/App
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package    Nano
 */


/**
 * A request object for Nano_App - simple accessors and utility
 *
 * <code>
 * SYNOPSYS:
 *
 *    $request = new Nano_App_Request();
 *
 *    list( $controller, $action, $id ) = $request->pathParts(3);
 *    if( $username = $request->post('username')) ...
 *
 *    $page = $request->page;
 *    alternatively: $request->get('page');
 *
 *    $remote_host = $request->header('Host');
 * </code>
 *
 * @class Nano_App_Request
 */
class Nano_App_Request {
    private $_request;
    private $_post;
    private $_get;
    private $_headers;
    private $_request_url;

    /**
     * Class constructor
     * Normally, you do not need to pass any arguments here -
     * To facilitate testing, however, you can.
     *
     * @param array   $OVERRIDES (optional) Overrides stuff from the superglobals - use for testing!
     */
    public function __construct( array $OVERRIDES = array() ) {
        $config = array_merge(array(
                'server'    => $_SERVER,
                'post'      => $_POST,
                'get'       => $_GET
            ), $OVERRIDES );

        $this->_server  = $config['server'];
        $this->_post    = $config['post'];
        $this->_get     = $config['get'];
    }


    /**
     * Magic auto getter - enables you to access methos of this class
     * as property ( e.g. $request->url ), or fetch request variables
     * from $_POST or $_GET otherwise.
     *
     * $_POST has precedence over $_GET in the latter case
     *
     * @param unknown $name
     * @return unknown
     */
    public function __get( $name ) {
        if ( method_exists( $this, $name ) ) {
            return $this->$name();
        }

        return $this->getValue( $name );
    }


    /**
     * Returns a boolean true if methos is POST
     *
     * @return bool $is_post
     */
    public function isPost() {
        if ( $this->method() == 'POST' ) {
            return true;
        }
        return false;
    }


    /**
     * Returns the path-parts found for $_SERVER[REQUEST_URI]
     * This function uses Nano_Url, and it's pathParts function
     *
     * @param unknown $padding (optional) Padding of the output, handy for list( $one, $two )
     * @return array $path_parts
     */
    public function pathParts( $padding = 0 ) {
        return $this->url->pathParts( null, $padding );
    }


    /**
     * Returns $_SERVER[REQUEST_METHOD] e.g. POST, GET
     *
     * @return string POST, GET, ...
     */
    public function method() {
        return $this->_server['REQUEST_METHOD'];
    }


    /**
     * Returns the entire $_POST array, or optionally fetches a single value
     * from it, in which case it may return null.
     *
     * @param string  $name (optional) Optional value to fetch @see Nano_Request::getValue
     * @return mixed Possibly: array $_POST, $_POST[$name] or null
     */
    public function post( $name = null ) {
        if ( $name ) {
            return $this->getValue( $name, 'POST' );
        }

        return $this->_post;
    }


    /**
     * Returns the entire $_GET array, or optionally fetches a single value
     * from it, in which case it may return null.
     *
     * @param string  $name (optional) Optional value to fetch @see Nano_Request::getValue
     * @return mixed Possibly: array $_GET, $_GET[$name] or null
     */
    public function get( $name=null ) {
        if ( $name ) {
            return $this->getValue( $name, 'GET' );
        }
        return $this->_get;
    }


    /**
     * Returns one specific header
     *
     * @param string  $name Header name to fetch
     * @return string Header value
     */
    public function header( $name ) {
        $headers = $this->headers();

        if ( key_exists( $name, $headers ) ) {
            return $headers[$name];
        }
    }



    /**
     *
     *
     * @return unknown
     */
    public function isAjax() {
        if ( $this->header('X-Requested-With') == 'XMLHttpRequest' ) {
            return true;
        }
        return false;
    }


    /**
     * Returns apache_request_headers -
     * for completeness, since you could call apache_request_headers yourself.
     *
     * @return array $request_headers
     */
    public function headers() {
        if ( null === $this->_headers ) {

            $this->_headers = $this->_apache_request_headers();
        }

        return $this->_headers;
    }



    /**
     *
     *
     * @return unknown
     */
    private function _apache_request_headers() {
        if ( function_exists( 'apache_request_headers') ) {
            return apache_request_headers();
        }

        $out = array();

        foreach ($_SERVER as $key=>$value) {
            if (substr($key, 0, 5)=="HTTP_") {
                $key=str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                $out[$key]=$value;
            }
        }
        return $out;
    }


    /**
     * Returns query value from either $_POST or $_GET
     * This an alias for Nano_App_Request::getValue
     *
     * @see Nano_App_Request::getValue
     * @param unknown $name    Query value to return
     * @param unknown $methods (optional) Whitelist ($_GET, $_POST)
     * @return mixed $_XXX[$name] or null
     */
    public function value( $name, $methods=array('POST', 'GET')) {
        return $this->getValue( $name, $methods );
    }


    /**
     * Returns query value from either $_POST or $_GET
     *
     * @param unknown $name    Query value to return
     * @param unknown $methods (optional) Whitelist ($_GET, $_POST)
     * @return mixed $_XXX[$name] or null
     */
    public function getValue( $name, $methods=array('POST', 'GET') ) {
        $methods = (array) $methods;

        if ( $this->isPost() && in_array('POST', $methods ) ) {
            if ( key_exists( $name, $this->_post ) ) {
                return $this->_post[$name];
            }
        }

        if ( in_array( 'GET', $methods ) && key_exists( $name, $this->_get ) ) {
            return $this->_get[$name];
        }
    }


    /**
     *
     *
     * @return unknown
     */
    public function documentRoot() {
        return $_SERVER['DOCUMENT_ROOT'];
    }


    /**
     * Returns $_SERVER[HTTP_HOST] and $_SERVER[REQUEST_URI] combined as a
     * single Nano_Url object.
     *
     * @return Nano_Url $request_url
     */
    public function url() {
        if ( null === $this->_request_url ) {
            $scheme = 'http';

            if ( isset($this->_server['HTTPS']) && $this->_server['HTTPS'] !== 'OFF' ) {
                $scheme = 'https';
            }

            $urlstring = sprintf('%s://%s/%s', $scheme, $this->_server['HTTP_HOST'], $this->_server['REQUEST_URI'] );
            $this->_request_url = new Nano_Url( $urlstring );
        }

        return $this->_request_url;
    }


}
