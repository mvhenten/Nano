<?php
$gd = new Nano_Gd(array('width' => 600, 'height' => 600, 'position' => array(120, 400)) );

function pngout( $gd ){
    header('Content-type: image/png');
    echo $gd->getImagePNG();
}


$config = array(
    'dbUser'    => 'survey',
    'dbPassword'    => 'survey',
    'dbName'        => 'survey'
);

//$db = new PDO( 'mysql:dbname=nano;host=localhost;', 'nano', 'nano' );

$db = new Nano_Db( $config );

$data = $db->fetchAll('
    SELECT
        sr.sound_id,
        sr.color_id,
        count(sr.color_id) as score,
        color.color
    FROM color
    LEFT JOIN survey_result sr ON sr.color_id = color.id
    LEFT JOIN sound s ON s.id = sr.sound_id
    GROUP BY color.id, sr.sound_id
    ORDER BY sound_id, color_id, score
');

//$data = $db->fetchAll( 'SELECT * FROM user');

//var_dump( $data );

$x = 300;
$y = 300;
$width = 100;
$height = 100;

$ny = $y - $height;

$a = 33;

$ox = cos( deg2rad($a) ) * $height;
$oy = sin( deg2rad($a) ) * $height;

//$gd->line( $x, $ny );
//$gd->setPosition( 300, 300 );
//$gd->line( $x+$ox, $y - $oy  );
// draw rectangle
function drawRect( $width, $height, $a ){
    global $gd;

    $a = deg2rad($a);
    //var_dump( $a ); exit;

    //$a = 180 + $a;
    $y = ( $height * sin($a) );
    $x = ( $height * cos($a) ) + $width ;
    $pos = clone $gd->getPosition();

    $points = array();
    $points[] = array( $width, 0 );
    $points[] = array( $x, -$y);
    $points[] = array(-($width-$x), -$y );
    $points[] = array(0, 0);

    $colors = array('ff0000', '00ff00', '0000ff', 'ffffff');

    $draw = array();
    foreach( $points as $value ){
        $draw[] = $pos->x + $value[0];
        $draw[] = $pos->y + $value[1];
    }
    $gd->polygon( $draw );
}

foreach( range(1, 5) as $x ){
    //$gd->setPosition( $x*30, 300);
    //drawRect( 20, 20, 60);
}

$size = 20;
foreach( $data as $obj ){
    //$gd->setPosition($obj->sound_id*($size+5), 400-$obj->score*($size+5));
    //$gd->setFill( $obj->color );
    //drawRect( $size, $size, 60 );

}

function translate( &$points, $translation, $operation = 'add' ){
    foreach( $points as $index => $point ){
        switch( $operation ){
            case 'add':
                $points[$index] = $point + array_shift($translation);
                break;
        }
    }
}

function move( &$points, $x, $y ){
    $translation = array();
    $move = array($x, $y);

    foreach( $points as $i => $point ){
        $translation[] = $move[$i%2];
    }

    translate( $points, $translation );
}

$points = array(
    100, 100,
    220, 100,
    220, 40,
    100, 40
);

function shear( &$points, $angle, $dir = 'x' ){
    global $gd;
    list( $sx, $sy, $x, $y, $a, $b ) = $points;

    $line = array( $x, $y, $a, $b );
    list( $x, $y, $a, $b ) = rotateLine( $line, $angle );

    $a = $a - $x;
    $b = $b - $y;

    $points[4]=$a;
    $points[5]=$b;

    $gd->setColor( 'cccccc');
    $gd->line( $x, $y, $a, $b );
    return;
    list($nx, $ny) = rotatePoint( array( $x, $y, $a, $b ), $angle );

    $points[4] = $points[2] + $nx;
    $points[5] = $points[3] + $ny;

//

    // first two points don't need displacement
  //  $translation = array_fill( 0, count($points), 0 );
/*
    for( $i = 4; $i < count($points); $i+=2){
        //$translation[$i]    = $nx;
        //$translation[$i+1]  = $ny;
    }

    $points[4] = $points[0] + $nx;
    $points[5] = $points[1] - $ny;

    //translate( $points, $translation );
*/
}

function rotatePoint( $line, $angle ){
    list( $x, $y, $a, $b ) = $line;

    $distX = $a - $x;
    $distY = $b - $y;

    $angle = deg2rad( $angle+270 );

    if( $distX !== 0 && $distY !== 0 ){
        $dist = sqrt(($distX*$distX+$distY*$distY));
    }
    else{
        $dist = $distX + $distY;
    }

    $xn = sin( $angle ) * $dist;
    $yn = cos( $angle ) * $dist;

    return array( $xn, $yn );
}


function rotateLineO( $line, $angle ){
    list( $x, $y, $a, $b ) = $line;

        $angle = deg2rad( $angle );

    $distX = $a - $x;
    $distY = $b - $y;

    if( $distX !== 0 && $distY !== 0 ){
        $dist = sqrt(($distX*$distX+$distY*$distY));
    }
    else{
        $dist = $distX + $distY;
    }


    if( $a < $x ){
        //$xn = (int)abs(sin( $angle ) * $dist);
        //$an = $x - $xn;
    }
    else{
        $xn = (int) (cos( $angle ) * $dist);
        $an = $x - $xn;
    }

    if( $b < $y ){
        $yn = (int) (sin( $angle ) * $dist);
        $bn = $y + $yn;
    }
    else{
        $yn = (int) (cos( $angle ) * $dist);
        $bn = $y - $yn;
    }


    echo "[$xn, $yn] for [$a,$b] => [$an, $bn] coming from $x and $y<br/>";

    return array( $x, $y, $b+$xn, $b+$yn);
    return array( $x, $y, $x+$xn, $y+$yn);
}

function rotateLine( $line, $angle ){
    $f = deg2rad( $angle );

    list( $x, $y, $a, $b ) = $line;

    $a = $a - $x;
    $b = $b - $y;

    $nx = $a * cos($f) - $b * sin($f);
    $ny = $b * cos($f) + $b * sin($f);

    //echo vsprintf( "Line: %d,%d to %d,%d<br/>", $line );
    //echo vsprintf( 'New: %d, %d, %d, %d<br/>', array( $line[0], $line[1], $nx, $ny));

    return array( $x, $y, $nx, $ny );
}


// points of a square
$points = array(
    100, 100,
    220, 100,
    220, 40,
    100, 40
);

$gd->polygon( $points );
move( $points, 130, 45 );
$gd->polygon( $points );
move( $points, -200, 140 );
$gd->polygon( $points );
move( $points, 130, 60 );
//$gd->polygon( $points, false);

//shear( $points, 33 );
//$gd->polygon( $points, false);


$o = array( 200, 200, 200, 250 );
//$o = array( 300, 300, 400, 300 );
$gd->line( $o[0], $o[1], $o[2], $o[3] );

$l = rotateLine($o, 10);
$gd->setColor('ff0000');
$gd->line( $l[0], $l[1], $l[2], $l[3] );

$l = rotateLine($o, 30);
$gd->setColor('00ff00');
$gd->line( $l[0], $l[1], $l[2], $l[3] );

$l = rotateLine($o, 45);
$gd->setColor('ff00ff');
$gd->line( $l[0], $l[1], $l[2], $l[3] );


/*
$l = rotateLine($o, 60);
$gd->setColor('0000ff');
//$gd->line( $l[0], $l[1], $l[2], $l[3] );

$l = rotateLine($o, 120);
$gd->setColor('ff00ff');
$gd->line( $l[0], $l[1], $l[2], $l[3] );

//coordinate system?
$l = rotateLine($o, 0 );
//$gd->setColor('00ffff');
//$gd->line( $l[0], $l[1], $l[2], $l[3] );

$l = rotateLine($o, 180 );
$gd->setColor('ffffff');
$gd->line( $l[0], $l[1], $l[2], $l[3] );
*/
pngout( $gd );
/*

// start at 300 x 300 draw rectangle
$gd->polygon(
    $x+$width, $y,
    $x+$width, $y-$height,
    $x, $y-$height
);

*/

?>
