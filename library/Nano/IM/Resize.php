<?php
/**
 * library/Nano/IM.php
 *
 * SYNOPSYS
 * <code>
 *
 *     my $im = new Nano_IM_Resize( $image_data, array(
 *       'width'         => null,
 *       'height'        => null,
 *       'subsampling'   => '4x4',
 *       'quality'       => 90,
 *       'format'        => 'jpeg'
 *    ));
 *
 *
 *  $jpeg_data = (string) $im;
 *  $im->format = 'png';
 *  $png_data  = (string) $im;
 *
 * </code>
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */

/**
 * This is a simple wrapper around a shell exec of imagemagic's "convert"
 *
 * Not all hosting environments provide proper support for Imagemagic libraries, so this
 * is a workaround for the time being.
 */
class Nano_IM_Resize {

    private $_config = array(
        'width'         => null,
        'height'        => null,
        'subsampling'   => '4x4',
        'quality'       => 90,
        'format'        => 'jpeg'
    );

    private $_source;

    public function __construct( $data, $config ) {
        $this->_source = $data;
        $this->setConfig( $config );
    }

    public function __set( $name, $value ){
        if( ( $method = "_set_$name" ) && method_exists( $this, $method ) ){
            return $this->$method( $value );
        }
        throw new Exception( "Property does not exist: $name" );
    }

    public function __get( $name ){
        if( isset( $this->_config[$name] ) ){
            return $this->_config[$name];
        }
    }

    public function __toString(){
        return $this->asString();
    }

    public function asString(){
        $cmd = sprintf('convert -resize %dx%d -sampling-factor %s -quality %d ');
    }

    private function _execute_resize(){
        $cmd = $this->_get_command();
        $descriptors = array(
           0 => array("pipe", "r"),
           1 => array("pipe", "w")
        );

        $process = proc_open( $cmd, $descriptors, &$pipes);

        if( is_resource( $process ) ){
            list( $dst, $src ) = $pipes;

            fwrite( $src );
            fclose( $src );

            $data = stream_get_contents( $dst );
            fclose($dst);

            $return_value = proc_close($process);
        }
        else{
            throw new Exception( "Cannot open proces: $cmd" );
        }

        return $data;
    }


    public function setConfig( array $config ){
        $config = array_merge( $this->_config, $config );

        foreach( $config as $key => $value ){
            $method = "_set_$key";
            $this->$method( $value );
        }
    }

    private function _get_command(){
        extract( $this->_config, EXTR_SKIP );
        return sprintf(
            'convert - -resize %dx%d -sampling-factor %s -quality %d %s:-',
            $widht, $height, $subsampling, $quality, $format
        );
    }

    private function _set_width( $value ){
        $this->_set_int( 'width', $value );
    }

    private function _set_height( $value ){
        $this->_set_int( 'height', $value );
    }

    private function _set_quality( $value ){
        $this->_set_int( 'quality', $value );
    }

    private function _set_subsampling( $value ){
        if( ! preg_match( '/^[1,2,4]x[1,2,4]$/', $value ) ){
            throw new Exception( 'Subsampling must be a valid factor like: 1x2,2x2,2x4');
        }
        $this->_config['subsampling'] = $value;
    }

    private function _set_format( $value ){
        if( ! in_array( $value, array( 'jpeg', 'png', 'gif' )) ){
            throw new Exception( "Unsupported image type: $value" );
        }
        $this->_config['format'] = $value;
    }

    private function _set_int( $name, $value ){
        if( ! is_int( $value ) ){
            throw new Exception( "Value for $name must be an integer" );
        }
        $this->_config[$name] = $value;
    }
}
