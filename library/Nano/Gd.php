<?php
/**
 * Basic OO wrapper around builtin gd functions.
 *
 * @file Nano/Gd.php
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
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package    Nano_Gd
 */


/**
 *
 *
 * @class Nano_Gd
 *
 *  Basic OO wrapper around builtin gd functions.
 *
 *  SYNOPSIS:
 *
 *  $gd = Nano_Gd::createFromPath( '/tmp/imgfile.jpg' );
 *  $thumbnail = $gd->resize( 100, null )
 *              ->crop( 100, 100 )
 *              ->getImageJPEG();
 *
 */
class Nano_Gd {
    protected $_resource;
    protected $dimensions;

    /**
     * Popular favorites
     */
    const IMAGESIZE_THUMBNAIL   = '120x120';
    const IMAGESIZE_ICON        = '64x64';
    const IMAGESIZE_SMALL       = '172x144';
    const IMAGESIZE_VIGNETTE    = '400x300';
    const IMAGESIZE_SD          = '640x480';
    const IMAGESIZE_HD          = '960x720';

    /**
     * class constructor
     *
     * @param string  $source   GD resource, imageblob, or path to image
     * @param boolean $fromPath (optional) Indicates that $source is a path
     */
    public function __construct( $source, $fromPath = true ) {
        if ( $fromPath && is_string( $source ) ) {
            $this->_createFromPath( $source );
        }
        else if ( is_resource( $source ) ) {
                $this->setResource( $source );
            }
        else {
            $this->_createFromString( $source );
        }
    }


    /**
     * Proxies some of gd's builtin functions as class methods for convenience
     *
     * @param string  $name Property to be acessed
     * @return mixed $value or NULL
     */
    public function __get( $name ) {
        if ( property_exists( $this, $name ) ) {
            if ( ( $method = 'get' . ucfirst( $name ) )
                && method_exists( $this, $method ) ) {
                return $this->$method();
            }
            return $this->$name;
        }
    }


    /**
     *  Invoke the setter function for $name if it exists
     *
     * @param string  $name  Property to be set
     * @param mixed   $value Value(s) to be set
     */
    public function __set( $name, $value ) {
        if ( property_exists( $this, $name ) ) {
            if ( ( $method = 'set' . ucfirst( $name ) )
                && method_exists( $this, $method ) ) {
                $value = (array) $value;
                call_user_func_array( array( $this, $method ), $value );
            }
        }
    }


    /**
     * Static factory method: Constructs a new Nano_Gd image from a
     * file
     *
     * @param string  $path Fully qualified path-to-image
     * @return unknown
     */
    public static function createFromPath( $path ) {
        return new Nano_Gd( $path, true );
    }


    /**
     * Static factory method: Constructs a new Nano_Gd image from raw data
     *
     * @param string  $source Image data string
     * @return unknown
     */
    public static function createFromString( $source ) {
        return new Nano_Gd( $source, false );
    }


    /**
     *
     *
     * @param unknown $path
     * @return unknown
     */
    public static function getInfo( $path ) {
        if ( file_exists( $path ) && false !== ( $info = getimagesize($path) ) ) {
            return $info;
        }
    }


    /**
     * Crop part of an image
     *
     *
     * @param int     $width  Width of the crop box
     * @param int     $height Height of the crop box
     * @param int     $x      (optional)      X offset of the crop
     * @param int     $y      (optional)     Y offset of the crop
     * @return Nano_Gd $image new instance
     */
    public function crop( $width, $height, $x = null, $y = null ) {
        if ( null !== ($gd = $this->getResource() ) ) {
            list( $w, $h ) = array_values( $this->getDimensions() );

            if ( null === $x && $w > $width ) {
                $x = intval(($w-$width)/2);
            }

            if ( null === $y && $h > $height ) {
                $y = intval(($h-$height)/2);
            }

            $target = imagecreatetruecolor( $width, $height );

            imagecopy( $target, $gd, 0, 0, $x, $y, $width, $height );

            return new Nano_Gd( $target );
        }
    }


    /**
     * Resize an image to the exact dimensions of $x and $y
     *
     *
     * @param int     $x (optional) Target width
     * @param int     $y (optional) Target height
     * @return Nano_Gd $resized Returns a new resized instance
     */
    public function resize( $x = null, $y = null) {
        if ( $x == null && $y == null ) {
            throw new Exception( 'You must provide either Width or Height' );
        }
        if ( null !== ($gd = $this->getResource()) ) {
            list( $width, $height ) = array_values($this->getDimensions());

            if ( $x && ! $y ) {
                $y = intval(( $x / $width ) * $height);
            }
            elseif ( $y && !$x ) {
                $x = intval(($y / $height ) * $width );
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
     * @param unknown $angle_degrees
     * @return Nano_Gd $resized Returns a new resized instance
     */
    public function rotate( $angle_degrees ) {
        if ( null !== ($gd = $this->getResource()) ) {
            $target = imagerotate( $gd, $angle_degrees, 0, false );
            $instance = new Nano_Gd( $target );

            return $instance;
        }
    }


    /**
     * Flip image horizontally or vertically
     *
     * @param int     $orientation (optional) 1 = vertical, 2 = horizontal, 3 = both
     * @return Nano_Gd $image new instance
     */
    public function flip( $orientation = 2 ) {
        if ( null !== ($gd = $this->getResource() ) ) {
            list( $width, $height ) = array_values( $this->getDimensions() );

            $target = imagecreatetruecolor( $width, $height );

            if ( $orientation == 2 || $orientation == 3 ) {
                $src_y      =    $height -1;
                $src_height =    -$height;
            }

            if ( $orientation == 1 || $orientation == 3 ) {
                $src_x       =    $width;
                $src_width   =    -$width;
            }

            imagecopyresampled( $target, $gd, 0, 0,
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
    public function flipHorizontal() {
        return $this->flip(2);
    }


    /**
     * Flip image vertically
     *
     * @see Nano_Gd::flip
     * @return Nano_Gd $image new instance
     */
    public function flipVertical() {
        return $this->flip(1);
    }


    /**
     * Flip image both vertically and horizontally
     *
     * @see Nano_Gd::flip
     * @return Nano_Gd $image new instance
     */
    public function flipBoth() {
        return $this->flip(3);
    }


    /**
     *
     *
     * @return array( $width, $height );
     */
    public function getDimensions() {
        if ( null !== ($gd = $this->getResource() ) ) {
            if ( !is_array( $this->dimensions ) ) {
                $this->dimensions = array(
                    'width'  => imagesx( $gd ),
                    'height' => imagesy( $gd )
                );
            }
        }
        return $this->dimensions;
    }


    /**
     *
     *
     * @param unknown $path
     */
    private function _createFromPath( $path ) {
        if ( file_exists( $path ) && false !== ($info = getimagesize($path)) ) {
            $this->_createFromString( file_get_contents($path) );

            $this->dimensions = array(
                'width'  => $info[0],
                'height' => $info[1]
            );
        }
        else {
            throw new Exception( "Unable to create image from '$path'" );
        }
    }


    /**
     *
     *
     * @param unknown $data
     */
    private function _createFromString( $data ) {
        $gd = imagecreatefromstring( $data );

        $this->setResource( $gd );
    }


    /**
     *
     *
     * @param unknown $gd
     */
    public function setResource( $gd ) {
        if ( is_resource( $gd ) && 'gd' == get_resource_type( $gd ) ) {
            if ( is_resource($this->_resource) ) {
                imagedestroy( $this->_resource );
            }
            $this->_resource = $gd;
        }
        else if ( ! is_resource($gd) ) {
                throw new Exception( 'Not a valid resource');
            }
        else {
            throw new Exception( 'Type of resource is ' . get_resource_type($gd));
        }

    }


    /**
     *
     *
     * @return unknown
     */
    public function getResource() {
        return $this->_resource;
    }


    /**
     * Output as an PNG image
     *
     * @return unknown
     */
    public function __toString() {
        return $this->imageOut( 'png', $quality, $path );
    }


    /**
     * Output as an PNG image
     *
     * @param string  $path (optional) Optional path
     * @return unknown
     */
    public function getImagePNG( $path = null ) {
        return $this->imageOut( 'png', 85, $path );
    }


    /**
     * Output as a jpeg image
     *
     * @param unknown $quality (optional)
     * @param unknown $path    (optional)
     * @return unknown
     */
    public function getImageJPEG( $quality = 85, $path = null ) {
        return $this->imageOut( 'jpeg', $quality, $path );
    }


    /**
     *
     *
     * @param unknown $quality (optional)
     * @param unknown $path    (optional)
     * @return unknown
     */
    public function getImageGIF( $quality = 85, $path = null ) {
        return $this->imageOut( 'gif', $quality, $path );
    }


    /**
     *
     *
     * @param unknown $type
     * @param unknown $quality (optional)
     * @param unknown $path    (optional)
     * @return unknown
     */
    public function imageOut( $type, $quality = 85, $path = null ) {
        if ( null !== ($gd = $this->getResource()) ) {
            $cmd = sprintf('image' . $type );
            if ( strlen($path) == 0 ) {
                ob_start();
            }

            $out = $cmd($this->getResource(), $path, $quality );

            if ( strlen($path) == 0 ) {
                return ob_get_clean();
            }
            return $out;
        }
        else {
            throw new Exception('Cannot create image, no resource available!');
        }
    }


}
