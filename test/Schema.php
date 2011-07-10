<?php
class Nano_Db_SchemaTest extends PHPUnit_Framework_TestCase{
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

    public function testSearch(){
        $model = new Model_Schema_Item();

        foreach( $model->search() as $item ){
            $this->assertType( 'Model_Schema_Item', $item );
        }

    }

    public function testSearchWhere(){
        $model = new Model_Schema_Item();

        foreach( $model->search( array('where' => array('slug' => 'schedule' )) ) as $item ){
            $this->assertEquals( 'schedule', $item->slug, 'Search returns expected result' );
        }

        $ids = array();
        foreach( $model->search( array('where' => array('id' => array('<', 99 ))) ) as $item ){
            $this->assertLessThan( 99, $item->id, "ID is less then 99" );
            $ids[] = $item->id;
        }

        foreach( $model->search( array('where' => array('id' => array('IN', $ids ))) ) as $item ){
            $this->assertTrue( in_array($item->id, $ids ), 'ID expected to be in array');
            $this->assertType( 'Model_Schema_Item', $item, 'ITEM is a Model_Schema_item' );
        }

        $sth = $model->search( array('where' => array('id' => array('<', 99 ))) );

        $this->assertType( 'PDOStatement', $sth );

        printf("ROWS: %s\n", $sth->rowCount());
        printf("QUERY: %s\n", $sth->queryString);


        //$pager = new Nano_Db_Pager( $sth );
        //
        //print 'COUNT: ' . $pager->count();
        //



    }
}








/*

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



    }


}
*/
//$test = new Test_test();
