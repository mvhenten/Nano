<?php
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
$sounds = $db->fetchAll('SELECT * FROM sound ORDER BY id ASC');

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

//set xtics   ("mai" 0.00000 -1, "jun" 1.00000 -1, "jul" 2.00000 -1, "aug" 3.00000 -1, "sep" 4.00000 -1, "okt" 5.00000 -1, "nov" 6.00000 -1, "des" 7.00000 -1, "jan" 8.00000 -1, "feb" 9.00000 -1, "mar" 10.0000 -1, "apr" 11.0000 -1)


if( isset( $_GET['data'] ) ){
    $tics = array();

    foreach( $labels['y'] as $index => $value ){
        $value = str_replace( array('.mp3', '_'), ' ', $value);
        $tics[] = sprintf('"%s" %d -1', $value, $index+1 );
    }
    echo "set key top left\n";
    echo "set xtics 1\n";
    echo "set ytics 1\n";
    echo "set xtics scale 3,2 rotate by 90\n";
    echo "set bmargin at screen 0.2\n";
    echo "set xrange[1:14]\n";
    echo "set yrange[0:25]\n";
    echo sprintf( "set xtics (%s)\n", join( ',', $tics ) );

    $labels['x'] = array_reverse( $labels['x'], true);

    $cmd = array();
    foreach( $labels['x'] as $x => $color ){
        $cmd[] = sprintf("'score.dat' index %d using 1:2 with lp lt rgb '%s' lw 2 title '%s'", $x-1, $color, $color );
    }

    echo 'plot ' . join( ",\\\n", $cmd );
    echo "\n";


    foreach( $labels['x'] as $x => $color ){
        $row = $set[$x];
        foreach( $row as $y => $z ){
            $label = str_replace( array('.mp3', '_'), ' ', $labels['y'][$y]);
            echo sprintf("%d\t%d\t%s\n", $y, $z, $label);
        }
        echo "\n\n";
    }
}
else{
    //sort($result);
    $html = array('<table border=1>');
    //sort( $sounds );
    foreach( $set as $x => $row ){
        $color = $labels['x'][$x];
        $html[] = sprintf( '<tr><th style="background-color: %s;">%d:%s</th>', $color,$x,$color );
        foreach( $row as $y => $z ){
            $html[] = sprintf('<td>%d</td>', $z);
        }
        $html[] = '</tr>';
    }

    $html[] = '<tr><th>c/s</th>';
    foreach( $labels['y'] as $y => $name ){
        $name = trim(str_replace(array('_', '.mp3'), ' ', $name ));

        $html[] = sprintf('<th>%d:%s</th>', $y, $name );

    }

    /*
    $html[] = '<tr><th>Color/sound</th>';
    foreach( $sounds as $sound ){
        $sound = trim(str_replace(array('_', '.mp3'), ' ', $sound ));

        $html[] = '<th>' . $sound . '</th>';
    }

    $html[] = '</tr></table>';
    */
    $html[] = '</table>';
    echo join( "\n", $html );

}
