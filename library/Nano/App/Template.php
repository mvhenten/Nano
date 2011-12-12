<?php
/**
 * library/Nano/App/Template.php
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
 * @package default
 */


if ( ! defined( 'APPLICATION_ROOT' ) ) {
	$nano_root = dirname( __FILE__ );
	define( 'APPLICATION_ROOT', dirname($nano_root) );
}

/**
 * Nano's Simple Template Engine
 *
 * @class Nano_App_Template
 *
 * Handles loading and rendering of templates. templates are simple php files
 * included within the scope of this class' render method. ideas are borrowed
 * from Django's templates and Template Toolkit templating.
 *
 * @todo Detailed synopsis
 *
*/
class Nano_App_Template {
	protected $_parents = array();
	protected $_blocks  = array();

	protected $_helpers = array();
	protected $_values = array();
	protected $_templatePath = '';
	protected $_helperPath   = array('helper');

	private $_templates = array();

	/**
	 * Class constructor.
	 * Reuqest object is not optional; other configuration parameters may be
	 * given as initialization arguments.
	 *
	 * @param array   $config (optional) A key/value array
	 */
	public function __construct( array $config = array() ) {
		foreach ( $config as $key => $value ) {
			$this->__set( $key, $value );
		}
	}


	/**
	 * Any values you need to carry over to the template are proxied trough
	 * magical __set and __get
	 *
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function __set( $key, $value ) {
		if ( ($method = 'set' . ucfirst($key) ) && method_exists( $this, $method ) ) {
			$this->$method( $value );
		}
		else if ( ($member = "_$key") && property_exists( $this, $member ) ) {
				$this->$member = $value;
			}
		else {
			$this->_values[$key] = $value;
		}
	}


	/**
	 * Any values you need to carry over to the template are proxied trough
	 * magical __set and __get
	 *
	 * @param unknown $key
	 * @return unknown
	 */
	public function __get( $key ) {
		if ( ($member = "_$key") && property_exists( $this, $member ) ) {
			return $this->$member;
		}
		else if ( key_exists( $key, $this->_values ) ) {
				return $this->_values[$key];
			}
	}


	/**
	 * The template class acts as a proxy for helper classes. A helper class may
	 * be factored by calling it's short name as a method of the current template
	 * class. The template class then takes care of loading and instantiating
	 * the helper.
	 *
	 *
	 * @param string  $name      Helper name, like 'url' for the url helper
	 * @param mixed   $arguments Optional creation arguments for url helpers
	 * @return void
	 */
	public function __call( $name, $arguments ) {
		$helper = $this->getHelper( $name );
		return call_user_func_array( array($helper, $name), $arguments );
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function __toString() {
		return $this->toString();
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function toString() {
		$collect = '';

		foreach ( $this->_templates as $template ) {
			$this->_parents = array( $template );

			while ( count( $this->_parents ) > 0 ) {
				$tpl = array_pop( $this->_parents );
				$content = $this->_include( $tpl, $this->_values );
			}

			$collect .= $content;
		}

		return $collect;
	}


	/**
     * Processes a  child-template -in-place- from the current
     * current scope. All variables available in the current scope will be
     * made available to the child template as well.
     *
     * This function produces output.
	 *
	 * @param unknown $tpl
	 */
	public function process( $tpl ) {
		echo $this->_include( $tpl, $this->_values );
	}


	/**
	 * Should have been called include ( but it cannot ) - after TT's include
	 *
	 * Processes a child-template in-place, but without expoding all variables
	 * from the current scope. Use the $scope_values instead.
	 *
	 * @param string $tpl
	 * @param array $scope_values (optional)
	 */
	public function integrate( $tpl, array $scope_values = array() ) {
		$scope_values = func_get_args();
		$tpl = array_shift( $scope_values );
		echo $this->_include( $tpl, $scope_values );
	}


	/**
	 *
	 *
	 * @param unknown $tpl_name
	 * @param array $scope_values
	 * @return unknown
	 */
	private function _include( $tpl_name, $scope_values ) {
		extract( $scope_values, EXTR_SKIP&EXTR_REFS  );

		$tpl_relative_path = preg_replace( '/^' . str_replace( '/', '\/', APPLICATION_PATH ) . '/', '', $tpl_name );
		$tpl_absolute_path = $this->expandPath( $tpl_relative_path );

		ob_start();
		ini_set( 'log_errors', 1 );
		ini_set( 'display_errors', 0);
		include $tpl_absolute_path;

		return ob_get_clean();
	}


	/**
	 * Renders the template; also cascades up to the templates
	 * this template may inherit from. Template names may be in the form of
	 * :modulename/:templatepath/:actionpath/:templatename, but without any
	 * file suffix and relative to the project directory
	 *
	 * @param unknown $name Simple template name
	 * @return string $rendered_output Rendered output
	 */
	public function render( $name ) {
		$this->_templates[] = $name;

		return $this;
	}


	/**
	 * Demarcates the start of a named content block in a template.
	 * Note that only the top parent template can render content outside
	 * of block regions.
	 *
	 * @param string  $name Name of the content region, such as "title", "navbar"
	 */
	public function block( $name ) {
		ob_start();
		if ( ! key_exists( $name, $this->_blocks ) ) {
			$this->_blocks[$name] = array();
		}
	}


	/**
	 * Announces the end of a named content block, adds the accumulated buffer
	 * to the _blocks array and flushes buffer.
	 *
	 * N.B. you must properly close each content region.
	 * N.B. This function produces output, altough during render output is buffered
	 *
	 * @return void
	 * @param string  $name        Name of the content region
	 * @param bool    $append=True Should content be inserted at the start or appended
	 */
	public function endBlock( $name, $append=True ) {
		$content = ob_get_clean();

		if ( $append ) {
			array_unshift( $this->_blocks[$name], $content );
		}
		else {
			$this->_blocks[$name][] = $content;
		}

		$string = join( "\n", array_reverse($this->_blocks[$name]) );
		echo $string;
	}


	/**
	 * Adds a parent template to the list of templates that must be rendered
	 *
	 * @param string  $name Relative template name. full path and suffix are added
	 */
	public function wrapper( $name ) {
		$this->_parents[] = $name;
	}


	/**
	 * Expands a template name into a template path; e.g. call templates by
	 * using a relative path starting at the application root.
	 *
	 * @param string  $name Basic name, like 'edit'. Omit file suffix. Nested names can be page/edit
	 * @return string $path Full path to /APPLICATION/$name.phtml
	 */
	private function expandPath( $name ) {
		$name = trim( $name, ' /\\');

		//$path = array(trim( $this->_templatePath, ' /\\'), );
		//$path = join( '/', array_filter($path));
		//return APPLICATION_PATH . '/' . $path;

		$path = join( '/', array_filter( array(
					APPLICATION_PATH,
					trim( $this->_templatePath, ' /\\'),
					$name . '.phtml'
				)));

		return $path;
	}


	/**
	 *
	 *
	 * @param unknown $template
	 * @return unknown
	 */
	public function templateExists( $template ) {
		$path = $this->expandPath( $template );

		return file_exists( $path );
	}


	/**
	 * Wrapper around the script helper
	 *
	 * @return unknown
	 */
	public function headScript() {
		return $this->getHelper( 'Script' );
	}


	/**
	 * Retrieves a helper class based on the single name for that helper
	 *
	 * @param string  $name Plain name; e.g. Nano_Helper_Script becomes 'script'
	 * @return Nano_Helper $helper
	 */
	public function getHelper( $name ) {

		if ( ! key_exists(strtolower($name) , $this->_helpers)  ) {
			$this->loadHelper( $name );
		}

		return $this->_helpers[strtolower($name)];
	}


	/**
	 * Performs a lookup, and instantiates a helper class with the simple name $name.
	 * e.g. 'script' will be expanded to match either 'Helper_Script' or even 'Nano_View_Helper'
	 *
	 * Classes that can be accessed trough $_helperPath will get precedence over
	 * classes from Nano_View_Helper_*
	 *
	 * @return void
	 * @param string  $name Simple helper name, e.g. like 'url'
	 */
	public function loadHelper( $name ) {
		$name   = ucfirst( $name );
		$helper = null;

		foreach ( Nano_Autoloader::getNamespaces() as $ns => $path ) {
			$klass = $ns . "_Helper_" . $name;

			if ( !class_exists( $klass ) ) {
				continue;
			}
			break;
		}

		if ( ! class_exists( $klass ) ) {
			$klass = 'Nano_View_Helper_' . $name;
		}

		if ( class_exists( $klass ) ) {
			$this->_helpers[strtolower($name)] = new $klass( $this );
		}
		else {
			throw new Exception( "Unable to resolve helper $name" );
		}
	}


	/**
	 *
	 *
	 * @param array   $values (optional)
	 */
	public function setValues( array $values = array() ) {
		$this->_values = $values;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function getValues() {
		return $this->_values;
	}


	/**
	 *
	 */
	public function clearValues() {
		$this->_values = array();
	}


	/**
	 *
	 *
	 * @param unknown $path
	 */
	public function addHelperPath( $path ) {
		$this->_helperPath[] = $path;
	}


	/**
	 *
	 *
	 * @param array   $paths (optional)
	 */
	public function setHelperPath( array $paths = array() ) {
		$this->_helperPath = $paths;
	}


	/**
	 *
	 *
	 * @param unknown $template
	 */
	public function setTemplate( $template ) {
		$this->_templates = (array) $template;
	}


	/**
	 *
	 *
	 * @param unknown $templates
	 */
	public function setTemplates( $templates ) {
		$this->_templates = (array) $template;
	}
}
