<?php
define( 'BASE_PATH', dirname(dirname(dirname(__FILE__))) );
require_once( BASE_PATH . '/library/Nano/Autoloader.php');
Nano_Autoloader::register();

class Nano_App_RouterTest extends PHPUnit_Framework_TestCase{
    private $_cases = array(
        '/(\w+)/(\w+)/(\d+)/?',
        '/bar/(\d+)/(\w+)/?',
        '/99/(\w+)/(\d+)/x',
        '/(\d+)/(\d+)/(\d+)/(\w+)',
        '/one/two/three/(\w+)',
        '/(x)/(.+)',
    );

    public function testConstruct(){
        $router = new Nano_App_Router(array( 'foo' => 'bar' ));
    }

    public function testMatches(){
        $cases = $this->_cases;

        $handlers = range( 'A', 'F');
        $routes   = array_combine( $this->_cases, $handlers );
        $router   = new Nano_App_Router($routes);

        $expected = array(
            'A' => array( '/foo/bar/99', array( 'foo', 'bar', 99 ) ),
            'B' => array( '/bar/42/biz/', array(  42, 'biz' ) ),
            'C' => array( '/99/biz/89/x', array( 'biz', 89 ) ),
            'D' => array( '/90/89/83/fitz', array( 90, 89, 83, 'fitz' )),
            'E' => array( '/one/two/three/do_99', array( 'do_99' )),
            'F' => array( '/x/y/z/123', array('x','y/z/123') )
        );

        foreach( $expected as $expected_handler => $values ){
            list( $uri, $expected_matches )     = $values;
            list( $handler, $matches, $match  ) = $router->getRoute( $uri );

            $this->assertEquals( $handler, $expected_handler, 'expected handler' );
            $this->assertEquals( $matches, $expected_matches, 'expected matches' );
            $this->assertEquals( $match, $uri, 'match matches full uri');
        }
    }
}
