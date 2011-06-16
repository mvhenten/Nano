<?php
require_once( dirname(dirname(__FILE__)) . '/library/Nano/Autoloader.php');
Nano_Autoloader::register();
include 'schema/Item.php';


$config = array(
    'dsn'      => 'mysql:dbname=pico;host=127.0.0.1',
    'username' => 'pico',
    'password' => 'pico'
);

Nano_Db::setAdapter( $config );

class Test_test extends Nano_Test {
    public $schema = null;


    public function a_test_schema(){
        $model = new Model_Schema_Item();
        $this->ok( 'instantiated class ok' );

        $this->dump( $model->columns() );
        $this->ok( 'retrieved columns');

    }


    public function test_search(){
        $model = new Model_Schema_Item();

        foreach( $model->search() as $item ){
            $this->log( get_class( $item ) );
            $this->log( sprintf('ID:Slug: %d, %s', $item->id, $item->slug ));
        }

        foreach( $model->search( array('where' => array('slug' => 'schedule' )) ) as $item ){
            $this->dump( $item->id );
        }


    }


}

$test = new Test_test();
