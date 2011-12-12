<?php
/**
 * t/App/Request.php
 *
 * @package tests
 */


define( 'BASE_PATH', dirname(dirname(dirname(__FILE__))) );
require_once BASE_PATH . '/library/Nano/Autoloader.php';
Nano_Autoloader::register();

class Nano_App_RequestTest extends PHPUnit_Framework_TestCase{
	private $_constructor_args = array(
		'server'    => array(
			'REQUEST_URI'       => '/foo/bar/23?biz=42&bar=34&ok=1',
			'REQUEST_METHOD'   => 'POST',
			'HTTP_HOST'         => 'example.com'
		),
		'post'      => array(
			'ok'        => '0',
			'message'   => 'Lorem ipsum, sit amet',
		),
		'get'       => array(
			'biz'   => '42',
			'bar'   => '34',
			'ok'    => '1',

		)
	);

	public function testConstruct() {
		$request = new Nano_App_Request();
		$this->assertType( 'Nano_App_Request', $request );
	}

	public function testPost() {
		$request = new Nano_App_Request( $this->_constructor_args );

		$this->assertEquals( $this->_constructor_args['post'],
			$request->post, 'post array is equal');

		foreach ( $this->_constructor_args['post'] as $key => $value ) {
			$this->assertEquals( $value, $request->post($key) );
			$this->assertEquals( $value, $request->$key );
		}
	}

	function testGetValuePost() {
		$args = $this->_constructor_args;
		unset($args['post']);

		$request = new Nano_App_Request($args);

		$this->assertEquals( $args['get']['ok'], $request->ok, 'FALLBACK to GET');
		$this->assertEquals( null, $request->post('ok'), 'no GET value in POST');
	}

	public function testGet() {
		$args = $this->_constructor_args;

		$request = new Nano_App_Request( $args );

		$this->assertEquals( $args['get'],
			$request->get, 'get array is equal');

		foreach ( $args['get'] as $key => $value ) {
			$this->assertEquals( $value, $request->get($key) );
		}
	}

	function testGetValueGet() {
		$args = $this->_constructor_args;
		$non_post_values = array_diff_key( $args['get'], $args['post'] );
		$request = new Nano_App_Request( $args );

		foreach ( $non_post_values as $key => $value ) {
			$this->assertEquals( $value, $request->$key, 'access trough __set ok' );
		}

		$post_not_get = array_intersect_key( $args['post'], $args['get'] );

		foreach ( $post_not_get as $key => $value ) {
			$this->assertNotEquals( $value, $request->get($key), 'post has precedence');
		}
	}

	public function testUrl() {
		$request = new Nano_App_Request( $this->_constructor_args );

		$this->assertType( 'Nano_Url', $request->url );

		$urlstring = sprintf('http://example.com%s', $this->_constructor_args['server']['REQUEST_URI']);

		$url = new Nano_Url( $urlstring );

		$this->assertEquals( $url->pathParts(), $request->pathParts );
	}

	public function test__get() {
		$request = new Nano_App_Request( $this->_constructor_args );

		foreach ( array('url', 'post', 'pathParts', 'get') as $call ) {
			$this->assertEquals( $request->$call(), $request->$call, '__get works fine');
		}
	}

}
