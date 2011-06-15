<?php
require_once( dirname(dirname(__FILE__)) . '/library/Nano/Autoloader.php');
Nano_Autoloader::register();

class Test_test extends Nano_Test {
    public $schema = null;

    public function a_test_schema(){
        include 'schema/Item.php';
        $this->ok( 'loaded schem class ok' );

        $model = new Model_Schema_Item();
        $this->ok( 'instantiated class ok' );

        $this->dump( $model->columns() );
        $this->ok( 'retrieved columns');
    }

    public function b_test_schema(){
        $this->ok( 'next' );
    }

}

$test = new Test_test();
