#!/usr/bin/env php
<?php
define( "APPLICATION_ROOT", dirname(__FILE__) ); // the root of the application
define( "APPLICATION_PATH", dirname(APPLICATION_ROOT)); //where the application is

require_once( APPLICATION_PATH . '/library/Nano/Autoloader.php');
Nano_Autoloader::register();

$opts = getopt('u:p:n:');
$dsn  = array_pop( $argv );

$namespace = is_string($opts['n']) ? $opts['n'] : 'Nano_Db';

$config = array(
    'dsn'      => $dsn,
    'username' => $opts['u'],
    'password' => $opts['p']
);

Nano_Db::setAdapter( $config );

$dbh = Nano_Db::getAdapter();
$tables = $dbh->fetchAll('show tables;', null, PDO::FETCH_ASSOC);
$tables = array_map( 'current' , $tables );

$collect = array();

foreach( $tables as $table ){
    $collect[$table] = $dbh->fetchAll(sprintf('DESCRIBE `%s`;', $table), null, PDO::FETCH_ASSOC);
}

$template = '<?
class %s_Schema_%s extends Nano_Db_Schema {
    private $_tableName = \'%s\';

    private $schema = array(%s
    );

    private $primary_key = array(
        array( %s )
    );
}';

foreach( $collect as $table => $schema ){
    $primary_key = array();
    $tbl_schema   = array();

    foreach( $schema as $col ){
        list( $field, $type, $null, $key, $default, $extra ) = array_values($col);

        preg_match('/(\w+).?(\d+)?/', $type, $matches );

        list($_, $type, $length ) = array_pad( $matches, 3, 0 );

        $required = $null == 'NULL' ? 'false' : 'true';


        $tbl_schema[] = "
        '$field' => array(
            'type'      => '$type',
            'length'    => $length,
            'default'   => '$default',
            'name'      => '$field',
            'extra'     => '$extra',
            'required'  => $required,
        )";

        if( $key == 'PRI' ){
            $primary_key[] = "'$field'";
        }
    }

    $klass = join('',array_map( 'ucfirst', explode( '_', $table )));

    $sc = sprintf($template,
        $namespace, $klass, $table,
        join(",\n", $tbl_schema ),
        join(',', $primary_key)
    );

    file_put_contents( "$klass.php", $sc );
}




//var_dump($collect);
