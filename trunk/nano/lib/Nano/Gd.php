<?php
/**
 * Class Nano_Gd
 *
 * This class provides a number of utility functions for working
 * with GD functions
 */
class Nano_Gd{
    const DEFAULT_WIDTH  = 400;
    const DEFAULT_HEIGHT = 300;
    const DEFAULT_COLOR  = '999999';
    const DEFAULT_FILL   = '666666';
    const DEFAULT_BACKGROUND_COLOR = 'FFFFFF';

    private $resource;
    private $position;
    private $dimensions;
    private $color;
    private $fill;
    private $height;
    private $width;
    private $backgroundColor;

    /**
     * class constructor
     * You may define defaults to override class constant default values
     */
    public function __construct( $arguments = array() ){
        foreach( $arguments as $key => $value ){
            $this->__set( $key, $value );
        }

        $this->draw( 'fill', 0, 0, $this->getBackgroundColor());
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
        $this->draw('destroy');
    }

    /**
     * Output as an PNG image
     */
    public function __toString(){
        return $this->getImagePNG();
    }

    /**
     * Output as an PNG image
     */
    public function getImagePNG(){
        return imagepng( $this->getResource() );
    }

    /**
     * draw a circle with current position as it's center
     *
     * @param int $radius Radius of the circle
     * @param bool $fill Use a solid color or just a line
     * @return void
     */
    public function circle( $radius, $fill = true ){
        $this->ellipse( $radius*2, $radius*2, $fill );
    }

    /**
     * draw an ellipse with current position as it's center
     *
     * @param int $widht Width of the ellipse
     * @param int $height Height of the ellipse
     * @param bool $fill Use a solid color or just a line
     * @return void
     */
    public function ellipse( $width, $height, $fill = true ){
        $method = 'filledellipse';
        $color  = $this->getFill();

        if( false == $fill ){
            $method = 'ellipse';
            $color  = $this->getColor();
        }

        $this->draw( $method,
            $this->getPosition()->x,
            $this->getPosition()->y,
            $width,
            $height,
            $color
        );
    }

    /**
     * Draw a filled polygon
     *
     * @todo implement a line-polygon for completeness sake
     * @param mixed $points This function can take a variable number of arguments
     *                      but there should be at least 4 values to draw a polygon
     * @return void
     */
    public function polygon( $points, $fill = true ){
        $fill = true;
        $points = func_get_args();
        if( is_array($points[0]) ){
            $fill   = isset($points[1]) ? $points[1] : true;
            $points = $points[0];
        }


        if( $fill ){
            // @todo: draw from current or not?
            // in polygons, this might be irritating.
            //$points[] = $this->getPosition()->x;
            //$points[] = $this->getPosition()->y;
            $num = floor( count( $points ) /2 );
            $this->draw('filledpolygon',
                $points, $num, $this->getFill()
            );
        }
        else{
            for( $i = 0; $i < count($points); $i += 2 ){
                list( $x, $y ) = array( $points[$i], $points[$i+1] );
                $n = isset( $points[$i+2] ) ? $points[$i+2] : $points[0];
                $m = isset( $points[$i+3] ) ? $points[$i+3] : $points[1];

                $this->line( $x, $y, $n, $m );
            }
        }
    }

    /**
     * Draw an arc
     *
     * @todo implement without fill
     *
     * @param int $width Width of the arc
     * @param int $height Height of the arc
     * @param int $start Start degrees ( 0 - 180 )
     * @param int $end End degrees ( 0, 180 )
     */
    public function arc( $width, $height, $start = 0, $end = 0, $style = IMG_ARC_PIE ){
        $this->draw( 'filledarc',
            $this->getPosition()->x,
            $this->getPosition()->y,
            $width,
            $height,
            $start,
            $end,
            $this->getFill(),
            $style
        );
    }

    /**
     * Draw a line from current position to $x, $y
     * This either takes 2 or 4 arguments. If 2 arguments are given,
     * current position is used to draw from.
     *
     * @param int $x y-coordinate to draw from OR to draw to
     * @param int $y y-coordinate to draw from OR to draw to
     * @param int $x2 x-coordinate to draw to
     * @param int $y2 y-coordinate to draw to
     * @return void
     */
    public function line( $args ){
        $args = func_get_args();

        if( count($args) == 1 && is_array($args[0]) ){
            $args = $args[0];
        }

        if( count($args) == 2 ){
            $pos = $this->getPosition();
            $args = array($pos->x, $pos->y, $args[0], $args[1]);
        }

        $this->draw('line',
            $args[0],
            $args[1],
            $args[2],
            $args[3],
            $this->getColor()
        );

        $this->setPosition( $args[0], $args[1] );
    }

    /**
     * Draw a rectangle using current position as upper-left corner
     *
     * @param int $width Width of the rectangle
     * @param int $height Height of the rectangle
     * @return void
     */
    public function rectangle( $width, $height ){
        $position = $this->getPosition();

        $this->draw('rectangle',
            $position->x,
            $position->y,
            $position->x + $width,
            $position->y + $height,
            $this->getColor()
        );
    }

    /**
     * Sets current position
     *
     * @param int $x X-coordinate
     * @param int $y Y-coordiante
     * @return void
     */
    public function setPosition( $x, $y ){
        $this->getPosition()->x = $x;
        $this->getPosition()->y = $y;
    }

    /**
     * Set background-color
     *
     * @param mixed $color Either an arra(r,g,b) or a hex color
     * @return void
     */
    public function setBackgroundColor( $color ){
        $color = func_get_args();
        list( $r, $g, $b ) = $this->parseColor( $color );
        $this->backgroundColor = $this->draw( 'colorallocate', $r, $g, $b );
    }

    /**
     * Set line color
     *
     * @param mixed $color Either an arra(r,g,b) or a hex color
     * @return void
     */
    public function setColor( $color ){
        $color = func_get_args();
        list( $r, $g, $b ) = $this->parseColor( $color );
        $this->color = $this->draw( 'colorallocate', $r, $g, $b );
    }

    /**
     * Set fill color
     *
     * @param mixed $color Either an arra(r,g,b) or a hex color
     * @return void
     */
    public function setFill( $color ){
        $color = func_get_args();
        list( $r, $g, $b ) = $this->parseColor( $color );
        $this->fill = $this->draw( 'colorallocate', $r, $g, $b );
    }

    public function getPosition(){
        if( null == $this->position ){
            $this->position = (object) array(
                'x' => 0,
                'y' => 0
            );
        }
        return $this->position;
    }

    protected function parseColor( $color ){
        if( count( $color ) == 1 ){
            $color = $color[0];
            if( !is_array( $color ) ){
                $color = $this->getRGBFromHex( $color );
            }
        }
        return $color;
    }

    /**
     * Main drawing function
     * Accepts a variable number of arguments
     *
     * First argument is parsed as the gd image... function
     * A resource is inserted as the first parameter, other parameters may be
     * provided as extra arguments tot this function
     *
     */
    protected function draw(){
        //@todo implement a call stack
        $arguments   = func_get_args();
        $method      = 'image' . array_shift( $arguments );
        $arguments   = array_merge( array( $this->getResource() ), $arguments );

        if( !function_exists( $method ) ){
            throw new Exception(sprintf('Method %s does not exist', $method));
        }

        return call_user_func_array( $method, $arguments );
    }

    protected function getBackgroundColor(){
        if( null == $this->backgroundColor ){
            $default = self::DEFAULT_BACKGROUND_COLOR;
            $this->setBackgroundColor( $default );
        }
        return $this->backgroundColor;
    }

    protected function getColor(){
        if( null == $this->color ){
            $default = self::DEFAULT_COLOR;
            $this->setColor( $default );
        }
        return $this->color;
    }

    protected function getFill(){
        if( null == $this->fill ){
            $default = self::DEFAULT_FILL;
            $this->setFill( $default );
        }
        return $this->fill;
    }

    private function getResource(){
        if( null == $this->resource ){
            $this->resource = imagecreatetruecolor(
                $this->getWidth(),
                $this->getHeight()
            );
        }
        return $this->resource;
    }

    // prevent resource being overwritten in the constructor
    private function setResource(){}

    protected function setWidth( $width ){
        //@todo implement scaling canvas
        if( isset( $this->width ) ) return;
        $this->width = (int) $width;
    }

    protected function setHeight( $height ){
        //@todo implement scaling canvas
        if( isset( $this->height ) ) return;
        $this->height = (int) $height;
    }

    public function getWidth(){
        if( null == $this->width ){
            $this->width = self::DEFAULT_WIDTH;
        }
        return $this->width;
    }

    public function getHeight(){
        if( null == $this->height ){
            $this->height = self::DEFAULT_HEIGHT;
        }
        return $this->height;
    }

    protected function getRGBFromHex( $hex ){
        if( is_object( $hex ) ){
            debug_print_backtrace();
            exit;
        }
        $hex = ltrim( $hex, '#' );

        $rgb = array();

        $rgb[] = hexdec( substr( $hex, 0, 2 ) );
        $rgb[] = hexdec( substr( $hex, 2, 2 ) );
        $rgb[] = hexdec( substr( $hex, 4, 2 ) );

        return $rgb;
    }
}
