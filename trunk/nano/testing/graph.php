<?php
$gd = new Nano_Gd_Graph(array('width' => 1000, 'height' => 820));
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
        color.color,
        s.name
    FROM color
    LEFT JOIN survey_result sr ON sr.color_id = color.id
    LEFT JOIN sound s ON s.id = sr.sound_id
    GROUP BY color.id, sr.sound_id
    ORDER BY color_id DESC
');

//$data = $db->fetchAll( 'SELECT * FROM user');

$colors = $db->fetchAll('SELECT * FROM color ORDER BY id DESC');
$sounds = $db->fetchAll('SELECT * FROM sound ORDER BY id DESC');

$result = array();

foreach( $data as $key => $value ){
    if( !isset($result[$value->color_id]) ){
        $result[$value->color_id] = array();
    }
    $result[$value->color_id][$value->sound_id] = $value->score;
}

$set = array();
// fill array with gaps
foreach( $colors as $color ){
    $x = $color->id;
    $labels['x'][$x] = $color->color;
    foreach( $sounds as $sound ){
        $y = $sound->id;
        $labels['y'][$y] = $sound->name;
        if( !isset( $result[$x][$y] ) ){
            $score = 0;
        }
        else{
            $score = $result[$x][$y];
        }
        $set[$x][$y] = (int) $score;
    }
}

//var_dump( $set[2][14] ); exit;

//var_dump( $labels['x'] ); exit;
//var_dump( $labels['y'] );
//echo '---';
//var_dump( $set[2] ); exit;


$gd->setData( $set );
$gd->setLabels( $labels );
$gd->setBarColors( $labels['x'] );
$gd->render();

pngout( $gd );

?>
