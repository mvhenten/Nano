#!/usr/bin/env php
<?php
define( "APPLICATION_ROOT", dirname(__FILE__) ); // the root of the application
define( "APPLICATION_PATH", dirname(APPLICATION_ROOT)); //where the application is

require_once( APPLICATION_PATH . '/library/Nano/Autoloader.php');
Nano_Autoloader::register();

array_shift( $argv );
$opts = getopt('u:p:n:');
$dsn  = array_pop( $argv );

if( ! $dsn ){
    usage();
    exit;
}

$namespace = is_string($opts['n']) ? $opts['n'] : 'Nano_Db';

$config = array(
    'dsn'      => $dsn,
    'username' => $opts['u'],
    'password' => $opts['p']
);

Nano_Db::setAdapter( $config );

$dbh = Nano_Db::getAdapter();
$sth = $dbh->query('show tables;');

$tables = $sth->fetchAll(PDO::FETCH_ASSOC);
$tables = array_map( 'current' , $tables );

$collect = array();

foreach( $tables as $table ){
    $collect[$table] = $dbh->query(sprintf('DESCRIBE `%s`;', $table))->fetchAll(PDO::FETCH_ASSOC);
}

//$collect = array('item_content' => $collect['item_content'] );
//var_dump($collect['item_content']);
//
//exit();


$template = '<?php
class %s_Schema_%s extends Nano_Db_Schema {
    protected $_tableName = \'%s\';

    protected $_schema = array(%s
    );

    protected $_primary_key = array(
        %s
    );

%s
}
';

foreach( $collect as $table_name => $schema ){
    print "Working on '$table_name'\n";

    $primary_key = array();
    $keys        = array();
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
        else if( $key == 'MUL' ){
            $keys[$field] = $field;
        }
    }

    $klass = join('',array_map( 'ucfirst', explode( '_', $table_name )));

    $functions = array();

    foreach( $keys as $col ){
        if( preg_match( '/(\w+)_(\w+)/', $col, $matches ) ){
            list( $field, $table, $col ) = $matches;

            $functions [] = preg_replace( '/^\s{13}/m', '', (sprintf("
                private function _get_%s(){
                    return \$this->has_a(array(
                        'key'         => '%s',
                        'table'       => '%s',
                        'foreign_key' => '%s'
                    ));
                }
            ", $table,$field, $table, $col
            )));
        }
    }


    $sc = vsprintf($template,
        array(
            $namespace, $klass, $table_name,
            join(",\n", $tbl_schema ),
            join(',', $primary_key),
            join("\n", $functions )
        )
    );

    //var_dump($sc);

    file_put_contents( "$klass.php", $sc );
}


function usage(){
printf("dump sql table as Nano_Db schema classes

example:
pull_schema.php -u username -p password -n App_Namespace  mysql:dbname=testdb;host=127.0.0.1
");
}
