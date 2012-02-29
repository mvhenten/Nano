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
 *       'subsampling'   => '2x2',
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
        'subsampling'   => '1x1',
        'quality'       => 90,
        'format'        => 'jpeg',
        'ignore_aspect' => false,
    );

    private $_source;

    /**
     * Class constructor.
     *
     * @param unknown $data
     * @param unknown $config (optional)
     */
    public function __construct( $data, $config = array() ) {
        $this->_source = $data;
        $this->setConfig( $config );
    }


    /**
     * Magic setter: acts as a proxy between private setters and public
     * member attributes.
     *
     * @param unknown $name
     * @param unknown $value
     * @return unknown
     */
    public function __set( $name, $value ) {
        if ( ( $method = "_set_$name" ) && method_exists( $this, $method ) ) {
            return $this->$method( $value );
        }
        throw new Exception( "Property does not exist: $name" );
    }


    /**
     * Proxy getter: exposes internal private $_config hash as public
     * member attributes.
     *
     * @param string  $name
     * @return mixed $name's value
     */
    public function __get( $name ) {
        if ( isset( $this->_config[$name] ) ) {
            return $this->_config[$name];
        }
    }


    /**
     * Returns this object as a "data" string.
     *
     * @return unknown
     */
    public function __toString() {
        return $this->asString();
    }


    /**
     * Returns this object as a "data" string.
     *
     * @return unknown
     */
    public function asString() {
        return $this->_execute_resize();
    }


    /**
     * Executes the resize command and returns image output
     *
     * @return binary $data
     */
    private function _execute_resize() {
        $cmd = $this->_get_command();

        $descriptors = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w")
        );

        $process = proc_open( $cmd, $descriptors, &$pipes);

        if ( is_resource( $process ) ) {
            list( $stdin, $stdout ) = $pipes;

            fwrite( $stdin, $this->_source );
            fclose( $stdin );

            $data = stream_get_contents( $stdout );
            fclose($stdout);

            $return_value = proc_close($process);
        }
        else {
            throw new Exception( "Cannot open proces: $cmd" );
        }

        return $data;
    }


    /**
     * Sets $config using private setters
     *
     * @param array   $config
     */
    public function setConfig( array $config ) {
        $config = array_merge( $this->_config, $config );

        foreach ( $config as $key => $value ) {
            $method = "_set_$key";
            $this->$method( $value );
        }
    }


    /**
     * Puts the convert commandline string together
     *
     * @return string
     */
    private function _get_command() {
        extract( $this->_config, EXTR_SKIP );
        return sprintf(
            'convert - %s -sampling-factor %s -quality %d %s:- ',
            $this->_get_resizeString($width, $height), $subsampling, $quality, $format
        );
    }


    /**
     * Creates an option for -resize that makes sense when leaving out either
     * $width or $height, and enforcing when both are provided together with $ignore_aspect
     *
     * @param unknown $width
     * @param unknown $height
     * @return unknown
     */
    private function _get_resizeString( $width, $height ) {
        $dimensions = $width  ? $width : '';
        $dimensions = $height ? "{$dimensions}x$height" : $dimensions;

        if ( strlen( $dimensions ) > 0 ) {
            if ( $width && $height && $this->_config['ignore_aspect'] ) {
                $dimensions = "$dimensions!";
            }

            return "-resize $dimensions";
        }
    }


    /**
     * Private setter called by __set
     *
     * @param bool    $value
     */
    private function _set_ignore_aspect( $value ) {
        if ( is_bool( $value ) ) {
            $this->_config['ignore_aspect'] = $value;
        }
    }


    /**
     * Private setter called by __set
     *
     * @param int     $value
     */
    private function _set_width( $value ) {
        if ( $value ) {
            $this->_set_int( 'width', $value );
        }
    }


    /**
     * Private setter called by __set
     *
     * @param int     $value
     */
    private function _set_height( $value ) {
        if ( $value ) {
            $this->_set_int( 'height', $value );
        }
    }


    /**
     * Private setter called by __set
     *
     * @param int     $value
     */
    private function _set_quality( $value ) {
        $this->_set_int( 'quality', $value );
    }


    /**
     * Private setter called by __set
     *
     * @param string  $value
     */
    private function _set_subsampling( $value ) {
        if ( ! preg_match( '/^[1,2,4]x[1,2,4]$/', $value ) ) {
            throw new Exception( 'Subsampling must be a valid factor like: 1x2,2x2,2x4');
        }
        $this->_config['subsampling'] = $value;
    }


    /**
     * Private setter called by __set
     *
     * @param string  $value
     */
    private function _set_format( $value ) {
        if ( ! in_array( $value, array( 'jpeg', 'png', 'gif' )) ) {
            throw new Exception( "Unsupported image type: $value" );
        }
        $this->_config['format'] = $value;
    }


    /**
     * Asserts that $name is an integer before setting it in the _config hash
     *
     * @param string  $name
     * @param int     $value
     */
    private function _set_int( $name, $value ) {
        if ( ! is_int( $value ) ) {
            throw new Exception( "Value for $name must be an integer" );
        }
        $this->_config[$name] = $value;
    }


}
