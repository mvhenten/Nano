<?php
$gd = new Nano_Gd(array('width' => 600, 'height' => 600, 'position' => array(200, 200)) );

function pngout( $gd ){
    header('Content-type: image/png');
    echo $gd->getImagePNG();
}

function dd( $msg, $pfx = '' ){
    static $fp;

    if( null == $fp ){
        $fp = fopen( '/tmp/debug_log.txt', 'a+' );
    }

    if( is_string( $msg ) ){
        $str = rtrim( $msg, "\n" );
    }
    else{
        $str = print_r( $msg, true );
    }

    if( strlen( $pfx ) > 0 ){
        $pfx = rtrim( $pfx, "\n" ) . ":\n";
    }

    $str = sprintf("%s%s\n", $pfx, $str );

    fwrite( $fp, $str );
}


//$gd->line( 0, $gd->getHeight()/2, $gd->getWidth, $gd->getHeight()/2  );


$gd->setColor( '#ECECEC');

for( $i = 10; $i < $gd->getWidth(); $i += 10 ){
    $gd->line($i, 0, $i, $gd->getHeight() );
}

for( $i = 10; $i < $gd->getHeight(); $i += 10 ){
    $gd->line(0, $i, $gd->getWidth(), $i );
}


$gd->setColor( '#CCCCCC' );
$gd->line(0, $gd->getHeight()/2, $gd->getWidth(), $gd->getHeight()/2 );
$gd->line($gd->getWidth()/2, 0, $gd->getWidth()/2, $gd->getHeight() );

list($xo,$yo) = array( $gd->getWidth()/2, $gd->getHeight()/2);

$gd->setColor( '#FF0000' );
/*
function rotate( $angle, $origin, $points ){
    list( $xo, $yo ) = $origin;
    $angle = deg2rad( $angle );

    list( $x, $y ) = $points;

    $s = $x - $xo;
    $a = sin( $angle ) * $s;
    $o = cos( $angle ) * $s;

    $xn = $xo + $a;
    $yn = $yo + $o;

    return array( $xn, $yn );
}
*/

function translate( $x, $y, $points ){
    $translation = array();

    while( $points ){
        $translation[] = $x + array_shift( $points );
        $translation[] = $y + array_shift( $points );
    }

    return $translation;
}

$center = array( $xo, $yo );

$gd->setPosition( $xo, $yo );

$gd->circle( 50, false );
$gd->circle( 100, false );

$p = array( $xo+50, $yo, $xo+90, $yo+40 );
list( $x, $y, $x1, $y1 ) = $p;

$gd->line($p);
$gd->setColor( '#00FF00');
// find d

// 1e punt
$a = deg2rad( 10 );

$d = sqrt((pow($x-$xo,2)+pow($y-$yo,2))); // ten opzichte van xo, xy

$t1 = $x - ($xo+$d);
$t2 = $y - $yo;

dd( array($t1, $t2), 'TRANSLATION1');


$x2 = $d * cos( $a );
$y2 = $d * sin( $a );

dd( array( $x2, $y2 ), 'XY1');
//$gd->line( $xo, $yo, $xo+$x2+$t1, $yo+$y2+$t2 );

function rotate( $angle, $center, $points ){
    list( $xo, $yo ) = $center;
    list( $x, $y )   = $points;

    $a = rad2deg(atan( ($y-$yo)/($x-$xo)));
    $a = deg2rad( $angle+$a );

    // make sure $y is on the x-axis
    //$yo = $yo + ( $y - $yo);


    $d = sqrt((pow($x-$xo,2)+pow($y-$yo,2)));

    $t1 = 1; //$d / ( $x - $xo );
    $t2 = 1;

    $x2 = ( $d * cos( $a ) ) * $t1;
    $y2 = ( $d * sin( $a ) ) * $t2;

    return array( $xo+$x2, $yo+$y2 );
}

// 2e punt

$origin = array($xo, $yo);
$angle = 90;
$xy1 = rotate($angle, $origin, array($x, $y) );
$xy2 = rotate($angle, $origin, array($x1, $y1) );
$gd->line( $xy1[0], $xy1[1], $xy2[0], $xy2[1] );

$gd->setColor( '#0000ff' );

$angle = 10;



$n = rotate($angle, $origin, array($x, $y) );
$xy2 = rotate($angle, $origin, array($x1, $y1) );

$n = array_merge( $n, $xy2 );

dd( $n, 'merged?');

$gd->line( $n );
//$gd->line( $xy1[0], $xy1[1], $xy2[0], $xy2[1] );

/*
// first, calculate the actual angle
$ra = atan( ($y1-$yo)/($x1-$xo) );
dd( rad2deg($ra), 'ATAN');
// calculate the diameter
$d = sqrt((pow($x1-$xo,2)+pow($y2-$yo,2)));
dd( $d );
$angle = 10 + rad2deg($ra);
$xy2 = rotate($angle, $origin, array($x1, $y1) );
$gd->line( $xy1[0], $xy1[1], $xy2[0], $xy2[1] );

*/

//$gd->line( $xy1[0], $xy1[1], $xy2[0], $xy2[1]);

$points = array( $xo + 120, $yo + 30, $xo + 180, $yo+90 );
//$gd->line( $points );
list( $x, $y, $x1, $y1 ) = $points;



foreach( range(0, 360, 10) as $angle ){
    $xy1 = rotate($angle, $origin, array($x, $y) );
    $xy2 = rotate($angle, $origin, array($x1, $y1) );

    //$gd->line( $xy1[0], $xy1[1], $xy2[0], $xy2[1]);
}

$points = array( $xo - 20, $yo - 30, $xo - 40, $yo-90 );
$points[] = $xo - 80;
$points[] = $yo - 50;

$gd->polygon( $points, false);

// now, rotate a polygon?
$gd->setColor( '#FF0000' );
$gd->line( $x, $y, $x1, $y1 );

$angle = 10;

foreach( range(0, 360, 30) as $angle ){
    $xy1 = rotate($angle, $origin, array($points[0], $points[1]) );
    $xy2 = rotate($angle, $origin, array($points[2], $points[3]) );
    $xy3 = rotate( $angle, $origin, array($points[4], $points[5]));

    $n = $xy1;
    $n = array_merge( $n, $xy2 );
    $n = array_merge( $n, $xy3 );

    //$gd->polygon( $n, false);
}
/*

$gd->line( $xy1[0], $xy1[1], $xy2[0], $xy2[1]);
$gd->line( $xy2[0], $xy2[1], $xy3[0], $xy3[1]);
$gd->line( $xy3[0], $xy3[1], $xy1[0], $xy1[1]);
*/



//2e punt
/*
$d = sqrt((pow($x1-$x,2)+pow($y2-$y,2))); // ten opzichte van xo, xy
$a = deg2rad( 10 );
$x3 = $d * cos( $a );
$y3 = $d * sin( $a );

dd( array( $x3, $y3 ), 'XY1');
$gd->line( $xo, $yo, $xo+$x3, $yo+$y3 );
 */

//$gd->line(  );

/*
$a = deg2rad( 20 );
$x2 = $d * cos( $a );
$y2 = $d * sin( $a );

$a = deg2rad( 30 );
$x2 = $d * cos( $a );
$y2 = $d * sin( $a );
$gd->line( $xo, $yo, $xo+$x2, $yo+$y2 );

$a = deg2rad( 40 );
$x2 = $d * cos( $a );
$y2 = $d * sin( $a );
$gd->line( $xo, $yo, $xo+$x2, $yo+$y2 );
 */

//$x2 = $x2+50;





//$gd->line( $xo+50, $yo+50, $xo+100, $yo+50);




pngout( $gd );
?>
