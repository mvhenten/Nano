<?php
class Nano_Db_Query_BuilderTest extends PHPUnit_Framework_TestCase{
    private $config;

    protected function setUp(){
        require_once( dirname(dirname(__FILE__)) . '/library/Nano/Autoloader.php');
        Nano_Autoloader::register();
        ////require_once(dirname(dirname(__FILE__)) . '/library/Nano/Db/Schema.php');
        //require_once('schema/Item.php');
        //
        //$this->config = array(
        //    'dsn'      => 'mysql:dbname=pico;host=127.0.0.1',
        //    'username' => 'pico',
        //    'password' => 'pico'
        //);
        //
        //Nano_Db::setAdapter( $this->config );
    }

    public function testConstruct(){
        $builder = new Nano_Db_Query_Builder();
    }

    public function testBuildWhere(){
        $builder = new Nano_Db_Query_Builder();

        $builder->select( 'id', 'slug' )
            ->from( 'item' )
            ->where( array('id' => array('IN', range(40,70) ) ) );

        printf("--\n%s\n", $builder );

        $builder->select( array('operator' => 'count') );

        printf("--\n%s\n", $builder );

        $builder->where( array('id' => array('<', 100 ) ) );
        printf("--\n%s\n", $builder );



        $builder->select( array('operator' => 'max', 'column' => 'id', 'table' => 'item') );
        printf("--\n%s\n", $builder );

        $builder->limit(10);
        printf("--\n%s\n", $builder );

        $builder->limit(10, 5);
        printf("--\n%s\n", $builder );

        $builder->offset(7);
        $builder->groupBy( 'slug' );


        printf("--\n%s\n", $builder );

        $builder = new Nano_Db_Query_Builder();

        $builder->delete()
            ->from('frobnitz')
            ->where(array('bla' => 1, 'biz' => 23 ));

        printf("--\n%s\n", $builder );



    }
}
