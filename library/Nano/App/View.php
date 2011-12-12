<?php
/**
 * Abstract base class for "view" or controller part.
 *
 * @file View.php
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
 * @package Nano_App
 */

/**
 *
 *
 * @class Nano_App_View
 *
 * Abstract base class for "view" or controller part.
 */
abstract class Nano_App_View {
	private $_response;
	private $_template;
	private $_request;

	/**
     * Class constructor.
     *
     * Nano_View is a bare view implementation that simply dispatches the request
     * to one function - get or post - and pushes the return value of that function
     * onto the response stack.
	 *
	 * @param Nano_App_Request  $request
	 * @param array   $extra   (optional)
	 */
	public function __construct( Nano_App_Request $request, array $extra = array() ) {
		$this->_request = $request;

		$this->response()->push( $this->dispatch( $request, $extra ) );
	}


	/**
	 * Code stub - dispatch to post/get based on request type.
	 *
	 * @param Nano_App_Request  $request
	 * @param unknown $extra
	 * @return string Response body
	 */
	protected function dispatch( Nano_App_Request $request, $extra ) {
		if ( $request->isPost() ) {
			return $this->post( $request, $extra );
		}

		return $this->get( $request, $extra );
	}


	/**
	 * Code stub - function to handle POST requests by default.
	 * This should be implemented.
	 *
	 * @return void
	 * @param Nano_App_Request  $request The request object
	 * @param Nano_Config $config  Extra data passed in App.
	 */
	protected function post( Nano_App_Request $request, $config ) {
		return;
	}


	/**
	 * Code stub - function to handle GET requests by default.
	 * This should be implemented.
	 *
	 * You may define your own custom request hook like "get{Action}" for
	 * specific request, or route all requests trough this function.
	 *
	 * @return void
	 * @param Nano_App_Request  $request The request object
	 * @param Nano_Config $config  Extra data passed in App.
	 */
	protected function get( Nano_App_Request $request, $config ) {
		return;
	}


	/**
     * Returns the current Nano_App_Response object
	 *
	 * @return Nano_App_Response
	 */
	public final function response() {
		if ( null == $this->_response ) {
			$this->_response = new Nano_Response();
		}

		return $this->_response;
	}


	/**
	 * Returns the current Nano_App_Template object
	 *
	 * @return Nano_App_Template $template
	 */
	public final function template() {
		if ( null == $this->_template ) {
			$this->_template = new Nano_App_Template(array('request'=>$this->request()));
		}

		return $this->_template;
	}


	/**
     * Returns the current Nano_App_Request object.
	 *
	 * @return Nano_App_Request $request
	 */
	public final function request() {
		return $this->_request;
	}


	/**
     * Factory method. Instantiates and returns a 'Model' or 'Schema' object
     * in one of the current namespaces, if possible.
     *
	 * @param string $name
	 * @param mixed $arguments (optional) Optional constructor arguments
	 * @return unknown
	 */
	public final function model( $name, $arguments = array() ) {
		foreach ( Nano_Autoloader::getNamespaces() as $ns => $val ) {
			$class_name = sprintf('%s_Model_%s', ucfirst($ns), ucfirst($name));
			if ( class_exists( $class_name )) {
				return new $class_name( $arguments );
			}

			$class_name = sprintf('%s_Schema_%s', ucfirst($ns), ucfirst($name));
			if ( class_exists( $class_name )) {
				return new $class_name( $arguments );
			}
		}
		throw new Exception( "Unable to resolve $name" );
	}


}
