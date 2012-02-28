<?php
class Nano_IM_ResizeTest extends PHPUnit_Framework_TestCase{
    private $config;

    protected $_img_path = '';

    protected function setUp(){
        $this->_img_path = dirname(__FILE__) . '/../resources/lena.jpg';
        require_once( dirname(__FILE__) . '../../../library/Nano/Autoloader.php');
        Nano_Autoloader::register();
    }

    public function testConstructor(){
        $image_data = file_get_contents( $this->_img_path );
        $im = new Nano_IM_Resize( $image_data, array(
          'width'         => 100,
          'height'        => 100,
          'subsampling'   => '4x4',
          'quality'       => 90,
          'format'        => 'jpeg'
        ));
    }

    public function testPublicAccessors(){
        $image_data = file_get_contents( $this->_img_path );
        $expected = array(
          'width'         => 100,
          'height'        => 100,
          'subsampling'   => '4x4',
          'quality'       => 90,
          'format'        => 'jpeg'
        );

        $im = new Nano_IM_Resize( $image_data, $expected );

        foreach( $expected as $key => $value ){
            $this->assertEquals( $im->$key, $value );
        }
    }

    public function testPublicSetters(){
        $image_data = file_get_contents( $this->_img_path );
        $expected = array(
          'width'         => 200,
          'height'        => 200,
          'subsampling'   => '2x2',
          'quality'       => 30,
          'format'        => 'png'
        );

        $im = new Nano_IM_Resize( $image_data, array( 'width' => 100, 'height' => 100 ) );

        foreach( $expected as $key => $value ){
            $im->$key = $value;
            $this->assertEquals( $im->$key, $value );
        }
    }



    //public function testResize(){
    //}
}
