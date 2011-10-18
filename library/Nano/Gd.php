<?php
/**
 * @file Nano/Gd.php
 *
 * Basic OO wrapper around builtin gd functions. Provides a number of utility
 * functions such as in-place-resize, and proxies some gd functions as class-methods.
 *
 * Copyright (C) <2011>  <Matthijs van Henten>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @category   Nano
 * @package    Nano_Gd
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 */
/**
 * @class Nano_Gd
 *
 *  Basic OO wrapper around builtin gd functions.
 */
class Nano_Gd{
    protected $_resource;
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

    public function crop( $x, $y, $width, $height ){
        if( null !== ($gd = $this->getResource() ) ){
            list( $w, $h ) = array_values( $this->getDimensions() );

            $target = imagecreatetruecolor( $width, $height );

            imagecopy( $target, $gd, 0, 0, $x, $y, $width, $height );

            return new Nano_Gd( $target );
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

            return $instance;
        }
    }

    /**
     * Rotate an image by n degrees
     *
     * @param int $degree Rotation angle (degrees)
     * @return Nano_Gd $resized Returns a new resized instance
     */
    public function rotate( $angle_degrees ){
        if( null !== ($gd = $this->getResource()) ){
            $target = imagerotate( $gd, $angle_degrees, 0, false );
            $instance = new Nano_Gd( $target );

            return $instance;
        }
    }

    /**
     * Flip image horizontally or vertically
     *
     * @param int $orientation 1 = vertical, 2 = horizontal, 3 = both
     * @return Nano_Gd $image new instance
     */
    public function flip( $orientation = 2 ){
        if( null !== ($gd = $this->getResource() ) ){
            list( $width, $height ) = array_values( $this->getDimensions() );

            $target = imagecreatetruecolor( $width, $height );

            if( $orientation == 2 || $orientation == 3 ){
                $src_y      =    $height -1;
                $src_height =    -$height;
            }

            if( $orientation == 1 || $orientation == 3 ){
                $src_x       =    $width;
                $src_width   =    -$width;
            }

            imagecopyresampled ( $target, $gd, 0, 0,
                $src_x, $src_y, $width, $height, $src_width, $src_height );

            return new Nano_Gd( $target );
        }
    }

    /**
     * Flip image horizontally
     *
     * @see Nano_Gd::flip
     * @return Nano_Gd $image new instance
     */
    public function flipHorizontal(){
        return $this->flip(2);
    }

    /**
     * Flip image vertically
     *
     * @see Nano_Gd::flip
     * @return Nano_Gd $image new instance
     */
    public function flipVertical(){
        return $this->flip(1);
    }

    /**
     * Flip image both vertically and horizontally
     *
     * @see Nano_Gd::flip
     * @return Nano_Gd $image new instance
     */
    public function flipBoth(){
        return $this->flip(3);
    }


    /**
     * @return array( $width, $height );
     */
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
            $this->_createFromString( file_get_contents($path) );

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
            if( is_resource($this->_resource) ){
                imagedestroy( $this->_resource );
            }
            $this->_resource = $gd;
        }
        else if( ! is_resource($gd) ){
            throw new Exception( 'Not a valid resource');
        }
        else{
            throw new Exception( 'Type of resource is ' . get_resource_type($gd));
        }

    }

    public function getResource(){
        return $this->_resource;
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

    /**
     * Output as an PNG image
     */
    public function __toString(){
        return $this->imageOut( 'png', $quality, $path );
    }

    /**
     * Output as an PNG image
     *
     * @param string $path Optional path
     */
    public function getImagePNG( $path = null ){
        return $this->imageOut( 'png', 85, $path );
    }

    /**
     * Output as a jpeg image
     */
    public function getImageJPEG( $quality = 85, $path = null ){
        return $this->imageOut( 'jpeg', $quality, $path );
    }

    public function getImageGIF( $quality = 85, $path = null ){
        return $this->imageOut( 'gif', $quality, $path );
    }

    public function imageOut( $type, $quality = 85, $path = null ){
        if( null !== ($gd = $this->getResource()) ){
            $cmd = sprintf('image' . $type );
            if( strlen($path) == 0 ){
                ob_start();
            }

            $out = $cmd($this->getResource(), $path, $quality );

            if( strlen($path) == 0 ){
                return ob_get_clean();
            }
            return $out;
        }
        else{
            throw new Exception('trying to create an image from nothing!');
        }
    }

}
