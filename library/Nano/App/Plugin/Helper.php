<?php
/**
 * library/Nano/App/Plugin/Helper.php
 *
 * @package default
 */


class Nano_App_Plugin_Helper {
	private $_plugins;

	/**
	 *
	 *
	 * @param array   $plugins (optional)
	 */
	public function __construct( array $plugins = array() ) {
		$this->_plugins = array_filter($plugins);
	}


	/**
	 *
	 *
	 * @param unknown $name
	 * @param unknown $args
	 * @return unknown
	 */
	public function __call( $name, $args ) {
		if ( method_exists( $this->_context, $name ) ) {
			return call_user_func_array( array( $this->_context, $name ), func_get_args() );
		}
	}


	/**
	 *
	 *
	 * @param unknown $name
	 * @return unknown
	 */
	public function __get( $name ) {
		if ( method_exists( $this->_context, $name ) ) {
			return $this->_context->$name();
		}
		if ( property_exists( $this->_context, $name )) {
			return $this->_context->$name;
		}
	}


	/**
	 *
	 *
	 * @param unknown $name
	 * @param unknown $context
	 */
	public function hook( $name, $context ) {
		$this->_setContext( $context );

		foreach ( $this->plugins() as $plugin_name ) {
			$this->_call_hook( $name, $plugin_name );
		}
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function plugins() {
		return array_keys($this->_plugins);
	}


	/**
	 *
	 *
	 * @param unknown $hook
	 * @param unknown $plugin_name
	 */
	private function _call_hook( $hook, $plugin_name ) {
		$scope_values = array('start' => false, 'end' => false);
		$scope_values[$hook] = true;

		extract( $scope_values, EXTR_SKIP&EXTR_REFS  );
		$path = join( '/', array($this->plugin_path(), $plugin_name, 'hook.php' ));

		ob_start();
		include $path;
		ob_clean();
	}


	/**
	 *
	 *
	 * @param unknown $context
	 */
	private function _setContext( $context ) {
		$this->_context = $context;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	private function plugin_path() {
		return APPLICATION_ROOT . '/plugin/';
	}


}
