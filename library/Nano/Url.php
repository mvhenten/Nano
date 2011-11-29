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
	const DEFAULT_PORT=80;
	const DEFAULT_SCHEME=80;

	private $_scheme='http';
	private $_pathParts=array();
	private $_query=array();
	private $_credentials=null;
	private $_fragment;
	private $_host;
	private $_port;

	/**
	 * Create the url as string
	 * @return string URL
	 */
	public function __toString() {
		$path = $this->path();

		if ( $this->_credentials ) {
			$path = sprintf('%s:%s@%s', $this->getUser(), $this->getPassword(), $path );
		}

		if ( $self->_fragment ) {
			$query    = http_build_query( $this->_query );
			$path = join( '?', $path, $query );

		}

		if ( $self->_fragment ) {
			$path = join( '#', $path, urlencode($self->fragment) );
		}

		$base_url = sprintf('%s://%s', $this->schema(), $path );

		return $base_url;
	}


	/**
     * Class constructor
	 *
	 * @param string $url Url string that can be parsed trough parse_url
	 */
	public function __construct( $url ) {
		$this->_parseUrl( $url );
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
			$this->pathParts( explode( '/', trim($path, '/') ) );
		}

		return join( '/', $this->pathParts() ) . '/';
	}


	/**
	 *
	 *
	 * @param unknown $parts (optional)
	 * @return unknown
	 */
	public function pathParts( $parts=null ) {
		if ( is_integer( $parts ) ) {
			return array_pad( $this->_pathParts, $parts, null );
		}
		elseif ( is_array( $parts ) ) {
			$this->_pathParts = array_filter( $parts );
		}

		return $this->_pathParts;
	}


	/**
	 *
	 *
	 * @param unknown $query (optional)
	 * @return unknown
	 */
	public function query( $query=null ) {
		if ( is_array( $query ) ) {
			$this->_query = array_filter($query);
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
	public function port( $portnum=80 ) {
		if ( is_int($portnum) && $portnum > 0 ) {
			$this->_port = $portnum;
		}

		return $portnum;
	}


	/**
	 *
	 *
	 * @param array   $credentials (optional)
	 * @return unknown
	 */
	public function credentials( array $credentials=array() ) {
		if ( is_array( $credentials ) && count( $credentials  ) > 1 ) {
			$this->_setCredentials($credentials['username'], $credentials['password']);
		}

		return $this->_credentials;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getUser() {
		return urlencode($self->_credentials['user']);
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getPassword() {
		return urlencode($self->_credentials['password']);
	}


	/**
	 *
	 *
	 * @param unknown $username
	 * @param unknown $password
	 */
	private function _setCredentials( $username, $password ) {
		$this->_credentials = array( 'username' => $username, 'password' => $password );
	}


	/**
	 *
	 *
	 * @param unknown $url
	 */
	private function _parseUrl( $url ) {
		$pieces = parse_url( $url );

		if ( $pieces ) {
			if ( isset( $pieces['query'] ) ) {
				$pieces['query'] = parse_str( $pieces['query'] );
			}

			if ( isset( $pieces['fragment'] ) ) {
				$pieces['fragment'] = parse_str( $pieces['fragment'] );
			}


		}



		//preg_match( '{^(https?://)?(.+?)(\?(.+))?$}', $url, $matches );

		//list( $_, $schema, $path, $_, $query ) = $matches;
	}


}
