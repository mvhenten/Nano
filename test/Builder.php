<?php
class Nano_Db_Query_BuilderTest extends PHPUnit_Framework_TestCase{
    private $config;

    protected function setUp(){
        require_once( dirname(dirname(__FILE__)) . '/library/Nano/Autoloader.php');
        Nano_Autoloader::register();
        //require_once(dirname(dirname(__FILE__)) . '/library/Nano/Db/Schema.php');
        require_once('schema/Item.php');

        $this->config = array(
            'dsn'      => 'mysql:dbname=pico;host=127.0.0.1',
            'username' => 'pico',
            'password' => 'pico'
        );

        Nano_Db::setAdapter( $this->config );
    }

    public function testConstruct(){
        $builder = new Nano_Db_Query_Builder();
    }

    public function testBuildWhere(){
        $builder = new Nano_Db_Query_Builder();

        $builder->select( 'id', 'slug' )
            ->from( 'item' )
            ->where( array('id' => array('IN', range(40,70) ) ) );

        echo "\n" . $builder;

        $builder->select( array('operator' => 'count') );

        echo "\n" . $builder;

        $builder->where( array('id' => array('<', 100 ) ) );
        echo "\n" . $builder;


        $builder->select( array('operator' => 'max', 'column' => 'id', 'table' => 'item') );
        echo "\n" . $builder;

    }
}
