<?php
/**
 * t/IM/Resize.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


class Nano_IM_ResizeTest extends PHPUnit_Framework_TestCase{
    private $config;

    protected $_img_path = '';

    /**
     *
     */
    protected function setUp() {
        $this->_img_path = dirname(__FILE__) . '/../resources/lena.jpg';
        require_once dirname(__FILE__) . '../../../library/Nano/Autoloader.php';
        Nano_Autoloader::register();
    }


    /**
     *
     */
    public function testConstructor() {
        $image_data = file_get_contents( $this->_img_path );
        $im = new Nano_IM_Resize( $image_data, array(
                'width'         => 100,
                'height'        => 100,
                'subsampling'   => '4x4',
                'quality'       => 90,
                'format'        => 'jpeg'
            ));
    }


    /**
     *
     */
    public function testPublicAccessors() {
        $image_data = file_get_contents( $this->_img_path );
        $expected = array(
            'width'         => 100,
            'height'        => 100,
            'subsampling'   => '4x4',
            'quality'       => 90,
            'format'        => 'jpeg',
            'ignore_aspect' => false,
        );

        $im = new Nano_IM_Resize( $image_data, $expected );

        foreach ( $expected as $key => $value ) {
            $this->assertEquals( $im->$key, $value );
        }
    }


    /**
     *
     */
    public function testPublicSetters() {
        $image_data = file_get_contents( $this->_img_path );
        $expected = array(
            'width'         => 200,
            'height'        => 200,
            'subsampling'   => '2x2',
            'quality'       => 30,
            'format'        => 'png',
            'ignore_aspect' => true
        );

        $im = new Nano_IM_Resize( $image_data, array( 'width' => 100, 'height' => 100 ) );

        foreach ( $expected as $key => $value ) {
            $im->$key = $value;
            $this->assertEquals( $im->$key, $value );
        }
    }


    /**
     *
     */
    public function testFormat() {
        $expected = array(
            array( 'format' => 'png' ),
            array( 'format' => 'jpeg' ),
            array( 'format' => 'gif' )
        );

        $image_data = file_get_contents( $this->_img_path );

        foreach ( $expected as $case ) {
            $filename   = tempnam( sys_get_temp_dir(), 'img');
            $im = new Nano_IM_Resize( $image_data, $case );
            file_put_contents( $filename, (string) $im );

            $info = getimagesize( $filename );
            unlink($filename);

            $this->assertEquals( $info['mime'], 'image/' . $case['format'] );
        }
    }


    /**
     *
     */
    public function testResizeDefaults() {
        $filename   = tempnam( sys_get_temp_dir(), 'img');
        $image_data = file_get_contents( $this->_img_path );

        $im = new Nano_IM_Resize( $image_data );
        file_put_contents( $filename, (string) $im );

        $this->assertEquals( getimagesize($filename), getimagesize( $this->_img_path ) );

        unlink($filename);
    }


    /**
     *
     */
    public function testResizeWidth() {
        $cases = array(
            array(
                'input'     => array( 'width' => 128 ),
                'expected'  => array( 'width' => 128, 'height' => 128 )
            ),
            array(
                'input'     => array( 'height' => 128 ),
                'expected'  => array( 'width' => 128, 'height' => 128 )
            ),
            array(
                'input'     => array( 'height' => 128, 'width' => 100 ),
                'expected'  => array( 'width' => 100, 'height' => 100 )
            ),
            array(
                'input'     => array( 'height' => 80, 'width' => 125, 'ignore_aspect' => true ),
                'expected'  => array( 'height' => 80, 'width' => 125 )
            ),
        );

        $image_data = file_get_contents( $this->_img_path );

        foreach ( $cases as $case ) {
            $filename   = tempnam( sys_get_temp_dir(), 'img');
            $im = new Nano_IM_Resize( $image_data, $case['input'] );

            file_put_contents( $filename, (string) $im );

            list( $width, $height ) = getimagesize( $filename );
            unlink($filename);

            foreach ( array( 'width' => $width, 'height' => $height ) as $key => $value ) {
                if ( isset( $case['expected'][$key] ) ) {
                    $this->assertEquals( $value, $case['expected'][$key] );
                }
            }
        }
    }



    //public function testResize(){
    //}
}
