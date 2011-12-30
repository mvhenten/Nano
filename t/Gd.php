<?php
class Nano_GdTest extends PHPUnit_Framework_TestCase{
    private $config;

    protected $_img_path = '';

    protected function setUp(){
        $this->_img_path = dirname(__FILE__) . '/resources/lena.jpg';
        require_once( dirname(dirname(__FILE__)) . '/library/Nano/Autoloader.php');
        Nano_Autoloader::register();
    }

    protected function _getGd(){
        return Nano_Gd::createFromPath( $this->_img_path );
    }


    public function testConstructors(){
        $gd = Nano_Gd::createFromPath( $this->_img_path );
        $this->assertEquals( array('width' => 512, 'height' => 512 ), $gd->dimensions, 'from path: dimensions are ok');

        $gd = Nano_Gd::createFromString( file_get_contents($this->_img_path) );
        $this->assertEquals( array('width' => 512, 'height' => 512 ), $gd->dimensions, 'from string: dimensions are ok');


        $gd = imagecreatefromstring( file_get_contents($this->_img_path) );

        $gd = new Nano_Gd($gd);
        $this->assertEquals( array('width' => 512, 'height' => 512 ), $gd->dimensions, 'from resource: dimensions are ok');

    }

    public function testCrop(){
        $gd = $this->_getGd()->crop( 200, 100 );
        $this->assertEquals( array('width' => 200, 'height' => 100 ), $gd->dimensions, 'crop resizes ok');
    }

    public function testResize(){
        $gd = $this->_getGd()->crop( 512, 256 );
        $gd = $gd->resize( 512, null );

        $this->assertEquals( array('width' => 512, 'height' => 256 ), $gd->dimensions, 'resize max width ok');

        $gd = $gd->resize( 256, null );
        $this->assertEquals( array('width' => 256, 'height' => 128 ), $gd->dimensions, 'resize max width ok');

        $gd = $this->_getGd()->crop( 512, 256 );

        $gd = $gd->resize( null, 128 );
        $this->assertEquals( array('width' => 256, 'height' => 128 ), $gd->dimensions, 'resize max height ok');

        $gd = $gd->resize( null, 64 );
        $this->assertEquals( array('width' => 128, 'height' => 64 ), $gd->dimensions, 'resize max height ok');
    }
}
