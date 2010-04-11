<?php

$config = array(
    'dbUser'    => 'nano',
    'dbPassword'    => 'nano',
    'dbName'        => 'nano'
);

//$db = new PDO( 'mysql:dbname=nano;host=localhost;', 'nano', 'nano' );

$db = new Nano_Db( $config );

$db->query('DROP TABLE IF EXISTS `color`;');
/*
foreach( $db->query( 'SELECT * FROM color') as $row ){
    var_dump( $row );
}
 */

$db->query('
CREATE TABLE `color` (
  `id` int(11) NOT NULL auto_increment,
  `type` int(11) NOT NULL,
  `color` varchar(32) default NULL,
  `name` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
');

foreach( range(0, 33 ) as $index ){
    $type = ( isset($type) && $type == 1 ) ? 2 : 1;

    $color = array();

    foreach( range(1, 3) as $value ){
        $value = rand(0,255);
        $color[] = sprintf('%02s', dechex($value - ($value % 42)));
    }

    $color = '#' . join('', $color );

    $db->insert( 'color', array(
        'type' => $type,
        'color' => $color,
        'name' => sprintf('Color #%d', $index )
    ));
}


$result = $db->fetchAll( 'SELECT id, type FROM color WHERE type = :type OR id > :id', array(':type' => 1, ':id' => 44 ));
$result = $db->select('color', 'type = ? OR id > ?', array(2,11) );


var_dump( $result );
