<?php
/**
 * Nano's autoloader class - loads Nano and more.
 *
 * @file library/Nano/Autoloader.php
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
 * @package    Nano_Autoloader
 */


if ( ! defined( 'APPLICATION_PATH' ) ) {
	define('APPLICATION_PATH', dirname(__FILE__) );
	define( 'APPLICATION_ROOT', dirname(APPLICATION_PATH));
}

/**
 * Defines a handler for spl_autoload
 *
 * @class Nano_Autoloader
 *
 *  Basic OO wrapper around builtin gd functions.
 */
class Nano_Autoloader {
	private static $instance;
	private $namespaces = array();

	/**
     * Class constructor - this is private, since this is a singleton
	 *
	 */
	private function __construct() {
	}


	/**
     * Register Nano namespace itself
     *
     * Convenience method, since it calls Nano_Autoloader::registerNamespace
	 */
	public static function register() {
		spl_autoload_register( 'Nano_Autoloader::autoLoad' );
		Nano_Autoloader::registerNamespace( 'Nano', dirname(__FILE__) );
	}


	/**
     * Singleton methos - returns Nano_Autoloader
	 *
	 * @return Nano_Autoloader $loader
	 */
	public static function getInstance() {
		if ( null === self::$instance ) {
			self::$instance = new Nano_AutoLoader();
		}

		return self::$instance;
	}


	/**
     * Autoload given $name. Name is the name of a class without namespace
	 *
	 * @param string $name Simple name of the class ( e.g. Gd for Nano_Gd )
	 */
	public static function autoLoad( $name ) {
		self::getInstance()->load( $name );
	}


	/**
	 * Returns all registered namespaces e.g. array( 'Nano', 'MyApp' )
	 *
	 * @return array $namespaces
	 */
	public static function getNamespaces() {
		return self::getInstance()->namespaces;
	}


	/**
	 * Register namespace - call this statically
	 *
	 * @param string $name Namespace, something like 'Nano'
	 * @param unknown $path Path where to look for implementations
	 */
	public static function registerNamespace( $name, $path ) {
		self::getInstance()->addNamespace( $name, $path );
	}


	/**
	 * Private method handler for registerNamespace
	 *
	 * @see Nano_Autoloader::registerNamespace
	 *
	 * @param string $name
	 * @param string $path
	 */
	private function addNamespace( $name, $path ) {
		$this->namespaces[$name] = $path;
	}


	/**
	 * Handler that actually attempts to autoload
	 *
	 * @param string $name
	 * @return unknown
	 */
	private function load( $name ) {
		$pieces = explode( '_', $name );

		foreach ( $this->namespaces as $key => $value ) {
			if ( strpos( $name, $key ) === 0 && strlen($name) > $key ) {
				$file = array_filter( explode('_', substr( $name, strlen($key) )) );
				$path = sprintf( '%s/%s.php', $value, join( '/', $file ));
				return $this->includePath( $path );
			}
		}

		$path = sprintf( '%s/%s.php', APPLICATION_ROOT, join( '/', $pieces ));
		return $this->includePath( $path, false );
	}


	/**
     * Safely attempts to include a file.
	 *
	 * @param string $path
	 * @param bool $fail (optional) Set this true if you need to fail hard
	 * @return bool $success True if succesfull
	 */
	private function includePath( $path, $fail = false ) {
		if ( file_exists( $path ) ) {
			require_once $path;
			$fail = false;
		}
		else if ( ($lower = strtolower($path)) && file_exists( $lower) ) {
				require_once $lower;
				$fail = false;
			}

		if ( $fail ) {
			throw new Exception( sprintf( 'File does not exist "%s"', $path ));
		}
		return !$fail;
	}
}
