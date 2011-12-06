<?php
/**
 * Simple URL object containing the base logic for url manipulation
 *
 * @file Nano/Url.php
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
 * @category   Nano
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 * @package    Nano
 */


class Nano_Url {
	private $_scheme = 'http';
    private $_path = '';
	private $_query=array();
	private $_fragment;
    private $_user;
    private $_password;
	private $_host;
	private $_port;

	/**
	 * Create the url as string
	 * @return string URL
	 */
	public function __toString() {
        return $this->_buildUrl();
	}

	/**
     * Class constructor
	 *
	 * @param string $url Url string that can be parsed trough parse_url
	 */
	public function __construct( $url ) {
		$this->parseUrl( $url );
	}

    public function __get( $name ){
        if( property_exists( $this, "_$name") && method_exists( $this, $name ) ){
            return $this->$name();
        }
    }

    public function __set( $name, $value ){
        if( property_exists( $this, "_$name") && method_exists( $this, $name ) ){
            return $this->$name( $value );
        }
    }

	/**
	 *
	 *
	 * @param unknown $url
	 */
	private function parseUrl( $url ) {
        foreach(  parse_url( $url ) as $method => $value ){
            $this->$method( $value );
        }
	}


	/**
     * Returns the scheme, sets the scheme if a valid string is provided
	 *
	 * @param unknown $scheme (optional)
	 * @return unknown
	 */
	public function scheme( $scheme=null ) {
		if ( is_string($scheme) && preg_match( '{^https?$}', $scheme ) ) {
			$this->_scheme = $scheme;
		}

		return $this->_scheme;
	}


	/**
	 *
	 *
	 * @param unknown $path (optional)
	 * @return unknown
	 */
	public function path( $path=null ) {
		if ( null !== $path && is_string( $path ) ) {
            $this->_path = trim( $path, '/' );
		}

		return '/' . $this->_path;
	}


	/**
	 *
	 *
	 * @param unknown $parts (optional)
	 * @return unknown
	 */
	public function pathParts( $parts=null, $padding=0 ) {
		if ( is_array($parts) ) {
			$this->_path = join( '/', array_filter($parts) );
		}

        $path_parts = explode( '/', trim($this->_path, '/') );

		if ( is_int($padding) && $padding > 0 ) {
			$path_parts = array_pad( $path_parts, $padding, null );
		}

        return $path_parts;
	}


	/**
	 *
	 *
	 * @param unknown $query (optional)
	 * @return unknown
	 */
	public function query( $query='' ) {
		if ( is_string( $query ) && !empty($query) ) {
            parse_str( $query, $query_hash );
			$this->_query = $query_hash;
		}

		return http_build_query( $this->_query );
	}

    public function query_form( array $query_form = array() ){
        if( count($query_form) > 0 ){
            $this->_query = $query_form;
        }

        return $this->_query;
    }


	/**
	 *
	 *
	 * @param unknown $fragment (optional)
	 * @return unknown
	 */
	public function fragment( $fragment=null ) {
		if ( is_string( $fragment ) ) {
			$this->_fragment = $fragment;
		}

		return $this->_fragment;
	}


	/**
	 *
	 *
	 * @param unknown $hostname (optional)
	 * @return unknown
	 */
	public function host( $hostname='' ) {
		if ( ! empty( $hostname ) ) {
			$this->_host = $hostname;
		}

		return $this->_host;
	}


	/**
	 *
	 *
	 * @param unknown $portnum (optional)
	 * @return unknown
	 */
	public function port( $portnum=null ) {
		if ( is_int($portnum) && $portnum > 0 ) {
			$this->_port = $portnum;
		}

		return $this->_port;
	}

    public function user( $user = null ){
        if( null !== $user ){
            $this->_user = $user;
        }

        return $this->_user;
    }

    public function pass( $password = null ){
        return $this->password( $password );
    }

    public function password( $password = null ){
        if( null !== $password ){
            $this->_password = $password;
        }

        return $this->_password;
    }

    private function _buildUrl(){
        return join( '', array(
            $this->scheme() . '://',
            join( '@', array(
                join( ':', array( $this->user, $this->password )),
                join( ':', array( $this->host, $this->port))
            )),
            join( '?', array(
                $this->path(),
                join( '#', array( $this->query, $this->fragment )),
            )),
        ));
    }
}
