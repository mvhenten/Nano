<?php
/**
 * Simple URL object containing the base logic for url manipulation -
 * wrapping parse_url, parse_str and http_build_query
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
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 * @package    Nano_Url
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
	 *
	 * @return string URL
	 */
	public function __toString() {
		return $this->_buildUrl();
	}


	/**
	 * Class constructor
	 *
	 * @param string  $url Url string that can be parsed trough parse_url
	 */
	public function __construct( $url ) {
		$this->parseUrl( $url );
	}


	/**
	 * Magic getter - syntactic sugar for accessing public accessors
	 * as properties
	 *
	 * @param unknown $name
	 * @return unknown Value if exists, or null
	 */
	public function __get( $name ) {
		if ( property_exists( $this, "_$name") && method_exists( $this, $name ) ) {
			return $this->$name();
		}
	}


	/**
	 * Magic setter - syntactic sugar to access public accessors as properties
	 *
	 * @param unknown $name
	 * @param unknown $value
	 */
	public function __set( $name, $value ) {
		if ( property_exists( $this, "_$name") && method_exists( $this, $name ) ) {
			$this->$name( $value );
		}
	}


	/**
	 * Parse url and set corresponding properties of this class
	 *
	 * @param string  $url
	 * @return Nano_Url $this This instance
	 */
	private function parseUrl( $url ) {
		foreach (  parse_url( $url ) as $method => $value ) {
			$this->$method( $value );
		}

		return $this;
	}


	/**
	 * Returns the scheme, sets the scheme if a valid string is provided
	 *
	 * @param string  $scheme (optional) - either 'http' or 'https'
	 * @return $scheme Current scheme
	 */
	public function scheme( $scheme=null ) {
		if ( is_string($scheme) && preg_match( '{^https?$}', $scheme ) ) {
			$this->_scheme = $scheme;
		}

		return $this->_scheme;
	}


	/**
	 * Get/sets the path component
	 *
	 * @param string  $path (optional)
	 * @return string $path Current path
	 */
	public function path( $path=null ) {
		if ( null !== $path && is_string( $path ) ) {
			$this->_path = trim( $path, '/' );
		}

		return '/' . $this->_path;
	}


	/**
	 * Get/sets the path segments as separarate pieces (pathparts)
	 *
	 * @param array   $parts   (optional) Optional new pathparts
	 * @param int     $padding (optional) Padds the output, if needed. Handy to use with list(...)
	 * @return array $path_parts Path segments of the current path
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
	 * Get/sets the query component as string
	 *
	 * @param string  $query (optional) New query component
	 * @return string $query Current query component
	 */
	public function query( $query='' ) {
		if ( is_string( $query ) && !empty($query) ) {
			parse_str( $query, $query_hash );
			$this->_query = $query_hash;
		}

		return http_build_query( $this->_query );
	}


	/**
	 * Get/Sets the query component as an array
	 *
	 * @see parse_str
	 *
	 * @param array   $query_form (optional) Optional new query parameters
	 * @return array $query_params Query parameters as array
	 */
	public function query_form( array $query_form = array() ) {
		if ( count($query_form) > 0 ) {
			$this->_query = $query_form;
		}

		return $this->_query;
	}


	/**
	 * Get/sets the url fragment component
	 *
	 * @param string  $fragment (optional)
	 * @return string $fragment
	 */
	public function fragment( $fragment=null ) {
		if ( is_string( $fragment ) ) {
			$this->_fragment = $fragment;
		}

		return $this->_fragment;
	}


	/**
	 * Get/Sets hostname component
	 *
	 * @param string  $hostname (optional) New hostname
	 * @return string $hostname
	 */
	public function host( $hostname='' ) {
		if ( ! empty( $hostname ) && is_string($hostname) ) {
			$this->_host = $hostname;
		}

		return $this->_host;
	}


	/**
	 * Get/Sets port number component
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


	/**
	 * Get/Sets the user component - used in basic auth
	 *
	 * @param string  $user (optional)
	 * @return string $username
	 */
	public function user( $user = null ) {
		if ( null !== $user && is_string($user) ) {
			$this->_user = $user;
		}

		return $this->_user;
	}


	/**
	 * Get/Sets pass component - alias for password to maintain symetry to parse_url
	 *
	 * @see Nano_Url::password
	 *
	 * @param string  $password (optional)
	 * @return string $password
	 */
	public function pass( $password = null ) {
		return $this->password( $password );
	}


	/**
	 * Get/Sets password component used in basic auth
	 *
	 * @see Nano_Url::password
	 *
	 * @param string  $password (optional)
	 * @return string $password
	 */
	public function password( $password = null ) {
		if ( null !== $password ) {
			$this->_password = $password;
		}

		return $this->_password;
	}


	/**
	 * Builds new url from components
	 *
	 * @return string $url
	 */
	private function _buildUrl() {
		return join( '', array(
				$this->scheme() . '://',
				join( '@', array_filter(array(
						join( ':', array_filter(array( $this->user, $this->password ))),
						join( ':', array_filter(array( $this->host, $this->port)))
					))),
				join( '?', array_filter(array(
						$this->path(),
						join( '#', array_filter(array( $this->query, $this->fragment ))),
					))),
			));
	}


}
