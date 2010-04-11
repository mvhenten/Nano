<?php
class Nano_Gd_Graph extends Nano_Gd{
    const DEFAULT_COL_SIZE = 20;
    const DEFAULT_BAR_COLOR = 'CCCCCC';
    const DEFAULT_SHEAR = 15;
    const DEFAULT_LEFT_OFFSET   = 400;
    const DEFAULT_BOTTOM_OFFSET = 40;

    private $data;
    private $colSize;
    private $bounds;
    private $colors;
    private $labels;

    public function setData( array $data ){
        $this->data = $data;
    }

    public function render(){
        $this->renderGrid();
        if( function_exists( 'imageantialias' ) ){
            $this->draw('antialias', true );
        }

        $data = $this->getData();

        foreach( $data as $x => $row ){
            foreach( $row as $y => $z ){
                $this->getBar( $x, $y, $z );
            }
        }
    }

    private function renderGrid(){

        $bound = $this->getBounds();
        foreach( range(1, 14) as $y ){
            $label = '';
            if( isset( $this->getLabels()->y[$y] ) ){
                $label = $this->getLabels()->y[$y];
            }
            $x = $bound->x - ( 100 + $y * $this->getShear() );
            $y = $bound->y - ( ($y-1) * $this->getColSize() );
            $this->line($x, $y, $x + 600,$y );
            // bool imagestring  ( resource $image  , int $font  , int $x  , int $y  , string $string  , int $color  )
            $this->draw('string', 3, $x, $y-16, $label, $this->getColor() );
        }
        foreach( range(1, 23) as $x ){
            $label = '';

            if( isset( $this->getLabels()->x[$x] ) ){
                $label = $this->getLabels()->x[$x];
            }

            $x = $bound->x - 15 + $x * ($this->getColSize()+2);
            $y = $bound->y + 50;
            $this->draw('stringup', 2, $x, $y+4, $label, $this->getColor() );

        }

        $x = $bound->x - 310;

        //$this->line( $x, 420, $x, 0);
        //@todo...
        /*
        $labels = $this->getLabels()->z;
        $y = 500;
        foreach( $labels as $id => $name ){
            $y -= $this->getColSize();
            $this->line( $x, $y, $x+6, $y+1 );
        }
        */
    }

    public function getData(){
        if( null == $this->data ){
            $this->data = array();
        }
        return $this->data;
    }

    public function setBarColors( array $colors ){
        $this->colors = $colors;
    }

    public function setLabels( array $labels ){
        $this->labels = (object) $labels;
    }

    public function getLabels(){
        if( null == $this->labels ){
            $this->labels = (object) array(
                'x' => array(),
                'y' => array(),
                'z' => array()
            );
        }
        return $this->labels;
    }

    public function getBarColor( $key ){
        if( null !== $this->colors ){
            if( isset( $this->colors[$key] ) ){
                return $this->colors[$key];
            }
        }

        return self::DEFAULT_BAR_COLOR;
    }

    private function dimColor( $color, $percent ){
        $rgb = $this->getRGBFromHex( $color );

        foreach( $rgb as $index => $value ){
            $rgb[$index] = round($percent * $value);
        }

        return $rgb;
    }

    private function getBar( $x, $y, $z ){
        $origx = $x;
        $origy = $y;
        $w = $this->getColSize();
        $color = $this->getBarColor( $x );

//        $this->setFill( $this->getBarColor( $x ) );
//        $this->setColor( $this->getBarColor( $x ) );
//- ($y*$this->getShear()
        $x = $this->getBounds()->x + ( $w*$x ) - ( $y * ($this->getShear()) );
        $y = $this->getBounds()->y - ($y*$w);



        $base = $this->getPolygon();
        $this->move( $base, $x, $y );
        $this->polygon( $base, true);
        $this->setColor( '#ffffff' );
        //$this->draw('string', 1, $x+8, $y, sprintf('[%d,%d]', $origy,$z), $this->getColor() );

        $this->setFill( $this->dimColor($color, 0.80) );

        $top = $this->getPolygon();
        $this->move( $top, $x, $y - $z*$w);

        $this->polygon( $top, true );

        $this->setFill( $color );

        $sidea = array(
            $base[0], $base[1],
            $base[6], $base[7],
            $top[6], $top[7],
            $top[0], $top[1],
        );

        $this->polygon( $sidea );

        $sideb = array(
            $base[6], $base[7],
            $base[4], $base[5],
            $top[4], $top[5],
            $top[6], $top[7],
        );

        $this->setFill( $this->dimColor($color, 0.7) );
        $this->polygon( $sideb );

        //$this->line( $base[0], $base[1], $base[6], $base[7]);
        $this->setColor( $this->dimColor($color, 0.90) );
        foreach( range( $base[1], $top[1], $this->getColSize() ) as $y ){
            $this->line( $base[0], $y, $top[6], $y+$this->getShear()+2);
        }
    }

    private function getPolygon(){
        $s = $this->getColSize();

        $points = array(
            0, 0,
            $s, 0,
            $s+$this->getShear(), $s-2,
            $this->getShear(), $s-2
        );

        return $points;

    }

    private function getColSize(){
        if( null == $this->colSize ){
            $this->colSize = self::DEFAULT_COL_SIZE;
        }
        return $this->colSize;
    }

    private function setBounds( array $bounds ){
        $this->bounds = (object) array(
            'x' => $bounds[0],
            'y' => $bounds[1]
        );
    }

    private function getBounds(){
        if( null == $this->bounds ){
            $this->setBounds(array( self::DEFAULT_LEFT_OFFSET, $this->getHeight() - 100 ));
        }
        return $this->bounds;
    }

    private function getShear(){
        return self::DEFAULT_SHEAR;
    }

    private function translate( &$points, $translation, $operation = 'add' ){
        foreach( $points as $index => $point ){
            switch( $operation ){
                case 'add':
                    $points[$index] = $point + array_shift($translation);
                    break;
            }
        }
    }

    private function move( &$points, $x, $y ){
        $translation = array();
        $move = array($x, $y);

        foreach( $points as $i => $point ){
            $translation[] = $move[$i%2];
        }

        $this->translate( $points, $translation );
    }
}
