<?php
class Nano_UrlTest extends PHPUnit_Framework_TestCase{
    private $config;

    protected function setUp(){
        require_once( dirname(dirname(__FILE__)) . '/library/Nano/Autoloader.php');
        Nano_Autoloader::register();
    }

    public function testConstruct(){
        $url = new Nano_Url( 'http://user:pw@example.com:8080?q=42#top');
    }

    public function testPieces(){
        $url_string = 'http://user:pw@example.com:8080/control/action?q=42#top';
        $url = new Nano_Url( $url_string );

        foreach( parse_url( $url_string ) as $method => $value ){
            $this->assertEquals( $value, $url->$method(), "$method returns $value" );
        }
    }

    public function testToString(){
        $url_string = 'http://user:pw@example.com:8080/control/action?q=42#top';
        $url = new Nano_Url( $url_string );

        $this->assertEquals( $url_string, (string) $url );
    }

    public function testGetterSetter(){
        $url_string = 'http://user:pw@example.com:8080/control/action?q=42#top';
        $url = new Nano_Url( $url_string );

        $expected = array(
            'scheme'    => 'https',
            'path'      => '/foo/bar/baz',
            'query'     => 'p=98&l=23',
            'fragment'  => 'bottom',
            'user'      => 'laurel',
            'password'  => 'hardy',
            'host'      => 'tutorial.info',
            'port'      => 1080,
        );

        foreach( $expected as $key => $value ){
            $url->$key = $value;
            $this->assertEquals( $value, $url->$key, 'value for $key is getset' );
        }
    }

    public function testPathParts(){
        $url_string = 'http://user:pw@example.com:8080/control/action?q=42#top';
        $url = new Nano_Url( $url_string );

        list( $control, $action, $id ) = $url->pathParts( null, 3);

        $this->assertEquals(
            array('control', 'action', null ),
            array( $control, $action, $id )
        );

        $this->assertEquals( $url_string, (string) $url );

        $url->pathParts( array( 'one', 'two', 90 ) );
        $url_string = 'http://user:pw@example.com:8080/one/two/90?q=42#top';

        $this->assertEquals( $url_string, (string) $url );
    }

    public function testQueryForm(){
        $url_string = 'http://example.com?q=42&foo=94&options[sugar]=1&options[coffee]=3';
        $url = new Nano_Url( $url_string );

        parse_str( $url->query, $expected_query );

        $this->assertEquals( $expected_query, $url->query_form() );

        $expected   = 'user=42&options%5Bmilk%5D=1&options%5Bsugar%5D=2';
        $query_form = array('user' => 42, 'options' => array( 'milk' => 1, 'sugar' => 2 ));

        $url->query_form( $query_form );

        $this->assertEquals( $expected, $url->query );
    }

}
