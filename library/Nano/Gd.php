<?php
/**
 * Class Nano_Gd
 *
 * This class provides a number of utility functions for working
 * with GD functions
 */
class Nano_Gd{
    protected $resource;
    protected $dimensions;

    /**
     * class constructor
     * You may define defaults to override class constant default values
     */
    public function __construct( $path, $fromPath = true ){
        if( $fromPath && is_string( $path ) ){
            $this->createFromPath( $path );
        }
        else if( is_resource( $path ) ){
            $this->setResource( $path );
        }
        else{
            $this->createFromString( $path );
        }
    }

    public static function getInfo( $path ){
        if( file_exists( $path ) && false !== ( $info = getimagesize($path) ) ){
            return $info;
        }
    }

    public function resize( $x = null, $y = null){
        if( $x == null && $y == null ){
            throw new Exception( 'You must provide either Width or Height' );
        }
        if( null !== ($gd = $this->getResource()) ){
            list( $width, $height ) = array_values($this->getDimensions());

            if( $x && ! $y ){
                $y = intval(( $x / $width ) * $height);
            }
            else if( $y && !$x ){
                $y = intval(($y / $height ) * $widht );
            }

            $target = imagecreatetruecolor( $x, $y );

            imagecopyresampled( $target, $gd, 0, 0, 0, 0, $x, $y, $width, $height );

            $instance = new Nano_Gd( $target );
//            $instance->setResource( $target );

            return $instance;
        }
    }

    public function getDimensions(){
        if( null !== ($gd = $this->getResource() ) ){
            if( !is_array( $this->dimensions ) ){
                $this->dimensions = array(
                    'width'  => imagesx( $gd ),
                    'height' => imagesy( $gd )
                );
            }
        }

        return $this->dimensions;
    }

    public function createFromPath( $path ){
        if( file_exists( $path ) && false !== ($info = getimagesize($path)) ){
            $this->createFromString( file_get_contents($path) );

            $this->dimensions = array(
                'width'  => $info[0],
                'height' => $info[1]
            );
        }
    }

    public function createFromString( $data ){
        $gd = imagecreatefromstring( $data );
        $this->setResource( $gd );
    }

    public function setResource( $gd ){
        if( is_resource( $gd ) && 'gd' == get_resource_type( $gd ) ){
            $this->destroy();

            $this->resource = $gd;
        }
        else if( ! is_resource($gd) ){
            throw new Exception( 'Not a valid resource');
        }
        else{
            throw new Exception( 'Type of resource is ' . get_resource_type($gd));
        }
    }

    public function getResource(){
        return $this->resource;
    }

    /**
     * Magic getter: invokes the getter function for $name
     * if it exists
     *
     * @param string $name Property to be acessed
     * @return mixed $value or NULL
     */
    public function __get( $name ){
        if( property_exists( $this, $name ) ){
            if( ( $method = 'get' . ucfirst( $name ) )
                && method_exists( $this, $method ) ){
                return $this->method;
            }
            return $this->$name;
        }
    }

    /**
     * Magic setter: invokes the setter function for $name if it exists
     *
     * @param string $name Property to be set
     * @param mixed $value Value(s) to be set
     */
    public function __set( $name, $value ){
        if( property_exists( $this, $name ) ){
            if( ( $method = 'set' . ucfirst( $name ) )
               && method_exists( $this, $method ) ){
                $value = (array) $value;
                call_user_func_array( array( $this, $method ), $value );
            }
        }
    }

    public function __destruct(){
        $this->destroy();
    }

    public function destroy(){
        if( null !== ($gd = $this->getResource()) ){
            imagedestroy( $this->getResource() );
        }
        $this->dimensions = null;
    }

    /**
     * Output as an PNG image
     */
    public function __toString(){
        return $this->imageOut( 'png', $quality, $path );
    }

    /**
     * Output as an PNG image
     */
    public function getImagePNG( $quality = 85, $path = null ){
        return $this->imageOut( 'png', $quality, $path );
    }

    public function getImageJPEG( $quality = 85, $path = null ){
        return $this->imageOut( 'jpeg', $quality, $path );
    }

    private function imageOut( $type, $quality = 85, $path = null ){
        if( null !== ($gd = $this->getResource()) ){
            $cmd = sprintf('image' . $type );
            if( strlen($path) == 0 ){
                ob_start();
            }

            $out = call_user_func( $cmd, $this->getResource(), $path, $quality );

            if( strlen($path) == 0 ){
                return ob_get_clean();
            }
            return $out;
        }
    }

}
