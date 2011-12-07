<?php
/**
 * library/Nano/Util/Ftp.php
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
 * @category   Nano/Util
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 * @package    Nano
 */


/**
 * Simple wrapper around php's php_* functions. Convenience, really
 *
 * @class Nano_Util_Ftp
 */

class Nano_Util_Ftp {
	private $_fp;

	/**
     * Class constructor -
     * Additional argumetns may be passed for Nano_Util_Ftp::connect
     *
	 * @see Nano_Util_Ftp::connect
	 * @param array $args (optional) If provided, arguments for Nano_Util_Ftp::connect
	 */
	public function __construct( array $args = array() ) {
		if ( is_array( $args ) && count($args) > 2 ) {
			call_user_func_array( array( $this, 'connect'), $args );
		}
	}


	/**
	 * Connects to server
	 *
	 * @param string  $host
	 * @param string  $username
	 * @param string  $password
	 * @param string  $pasv     (optional)
	 * @return mixed False on faillure
	 */
	public function connect( $host, $username, $password, $pasv=true ) {
		$fp = ftp_connect($host);

		if ( $fp && ftp_login($fp, $username, $password ) ) {
			$this->_fp = $fp;

			if ( $pasv ) {
				$this->pasv();
			}

			return $this;
		}

		return false;
	}


	/**
	 * Login on current connection -
	 * Returns success or faillure
	 *
	 * @param string  $username
	 * @param string  $password
	 * @return bool $success
	 */
	public function login( $username, $password ) {
		return ftp_login( $this->_fp, $username, $password );
	}


	/**
	 * Sets pasv true/false
	 *
	 * @param string  $pasv (optional) defaults to true
	 * @return $this
	 */
	public function pasv( $pasv = true ) {
		ftp_pasv( $this->_fp, $pasv );
		return $this;
	}


	/**
	 * Disconnects
	 *
	 * @return $this
	 */
	public function quit() {
		ftp_quit( $this->_fp );
		return $this;
	}


	/**
	 * Change directory
	 *
	 * @param string  $path Relative to cwd
	 * @return $this
	 */
	public function chdir( $path ) {
		ftp_chdir( $this->_fp, ftp_pwd($this->_fp) . '/' . $path );
		return $this;
	}


	/**
	 * Current working directory
	 *
	 * @see ftp_pwd
	 *
	 * @return string $current_dir
	 */
	public function cwd() {
		return ftp_pwd( $this->_fp );
	}


	/**
	 * Current working directory
	 *
	 * @see ftp_pwd
	 *
	 * @return string $current_dir
	 */
	public function pwd() {
		return ftp_pwd( $this->_fp );
	}


	/**
	 * Gets the filecontents of $path, where $path is relative to current
	 * working directory
	 *
	 * @param string  $path Filename/path relative to cwd
	 * @return string $filecontents
	 */
	public function get( $path ) {
		$tmp    = tempnam(sys_get_temp_dir(), 'PHP_');
		$handle = fopen($tmp, 'w');
		$path   = ftp_pwd($this->_fp) . '/'. $path;

		ftp_fget($this->_fp, $handle, $path, FTP_ASCII, 0);
		fclose($handle);

		$content = file_get_contents( $tmp );
		unlink( $tmp );

		return $content;
	}


	/**
	 * Puts content to $path
	 *
	 * @param string  $content
	 * @param string  $path    Absolute path on server
	 * @return $this
	 */
	public function put( $content, $path ) {
		$tmp = tempnam( sys_get_temp_dir(), 'PHP_' );

		file_put_contents( $tmp, $content );

		$fp = fopen( $tmp, 'r');

		ftp_fput( $this->_fp, $path, $fp, FTP_BINARY, 0 );

		fclose( $fp );
		unlink( $tmp );

		return $this;
	}


	/**
	 * Checks if path is a file
	 *
	 * @param string  $path Relative to pwd
	 * @return bool $is_file
	 */
	public function isFile( $path ) {
		return ftp_size( $this->_fp, $path ) == -1 ? false : true;
	}


	/**
	 * Checks if path is a directory
	 *
	 * @param string  $path Relative to pwd
	 * @return bool $is_file
	 */
	private function isDir( $path ) {
		if ( @ftp_chdir( $this->_fp, basename($path) ) ) {
			ftp_chdir( $this->_fp, '..' );
			return true;
		}
		return false;
	}


	/**
	 * Lists directory contents - and tells you if it's a file or directory
	 *
	 * @return array $dirlisting array( filename => type, dirname => type );
	 */
	public function dir() {
		if ( $this->_fp == null ) {
			return null;
		}

		$list = ftp_nlist( $this->_fp, '.' );
		$collect = array();

		foreach ( $list as $file ) {
			$collect[$file] = ftp_size($this->_fp, $file) == -1 ? 'dir' : 'file';
		}

		return $collect;
	}


}
