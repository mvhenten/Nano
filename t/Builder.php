<?php
/**
 * t/Builder.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


/**
 *
 *
 * @todo This part of nano needs a lot more attention and testing.
 *
 */
ini_set('display_errors', "true");
ini_set('display_warnings', "true");

class Nano_Db_Query_BuilderTest extends PHPUnit_Framework_TestCase{
    private $config;

    /**
     *
     */
    protected function setUp() {
        require_once dirname(dirname(__FILE__)) . '/library/Nano/Autoloader.php';
        Nano_Autoloader::register();
    }


    /**
     *
     */
    public function testConstruct() {
        $builder = new Nano_Db_Query_Builder();
    }


    /**
     *
     *
     * @return unknown
     */
    private function _builder() {
        return new Nano_Db_Query_Builder();
    }


    /**
     *
     *
     * @param unknown $str
     */
    private function _print( $str ) {
        foreach ( explode("\n", (string) $str ) as $s ) {
            print "\n$s";
        }
    }


    /**
     *
     */
    public function testInsert() {
        $values = array('start' => 1, 'finish' => 4, 'duration' => 3);
        $table  = 'foobar';

        $builder = $this->_builder()
        ->insert( $table, $values );

        $expect =
            'INSERT INTO `foobar` ( `start`,`finish`,`duration` ) VALUES ( ?,?,? )';

        $this->assertEquals( (string) $builder, $expect );
        $this->assertEquals( count($builder->bindings()), 3);
        $this->assertEquals( $builder->bindings(), array(1, 4, 3));
    }


    /**
     *
     */
    public function testDelete() {
        $values = array('author' => 1, 'publisher' => 2 );
        $table  = 'books';

        $builder = $this->_builder()
        ->delete( $table )
        ->where( $values );

        $expect =
            "DELETE\nFROM `books`\nWHERE `author` = ? AND `publisher` = ?";

        $this->assertEquals( (string) $builder, $expect );
        $this->assertEquals( count($builder->bindings()), count( $values ) );
        $this->assertEquals( $builder->bindings(), array_values( $values ) );
    }


    /**
     *
     */
    public function testOrderPlain() {
        $columns = array( 'title', 'isbn' );
        $table  = 'books';
        $order = 'date';

        $builder = $this->_builder()
        ->select( $columns )
        ->from( $table )
        ->order( $order );

        $expect =
            "SELECT a.`title`,a.`isbn`\nFROM `books` a\nORDER BY `date`";

        $this->assertEquals(  $expect, $builder->sql() );
        $this->assertEquals( count($builder->bindings()), 0 );
        $this->assertEquals( $builder->bindings(), array() );
    }


    /**
     *
     */
    public function testOrderTableCol() {
        $columns = array( 'title', 'isbn' );
        $table  = array( 'books');
        $order = array( 'table' => 'books', 'col' => 'published' );

        $builder = $this->_builder()
        ->select( $columns )
        ->from( $table )
        ->order( $order );

        $expect =
            "SELECT a.`title`,a.`isbn`\nFROM `books` a\nORDER BY a.`published`";

        $this->assertEquals(  $expect, $builder->sql() );
        $this->assertEquals( count($builder->bindings()), 0 );
        $this->assertEquals( $builder->bindings(), array() );
    }


    /**
     *
     */
    public function testOrderRAND() {
        $columns = array( 'title', 'isbn' );
        $table  = 'books';
        $order = 'RAND()';

        $builder = $this->_builder()
        ->select( $columns )
        ->from( $table )
        ->order( $order );

        $expect =
            "SELECT a.`title`,a.`isbn`\nFROM `books` a\nORDER BY RAND()";

        $this->assertEquals(  $expect, $builder->sql() );
        $this->assertEquals( count($builder->bindings()), 0 );
        $this->assertEquals( $builder->bindings(), array() );
    }


    /**
     *
     */
    public function testUpdate() {
        $builder =
            $this->_builder()
        ->update('foobar', array('id'=>1, 'blaza'=>39))
        ->where( array('fitz'=>13) );

        $expect = "UPDATE `foobar` SET\n"
            . "`id` = ?,\n"
            . "`blaza` = ?\n"
            . "WHERE `fitz` = ?";

        $this->assertEquals( (string) $builder, $expect );
        $this->assertEquals( count($builder->bindings()), 3);
        $this->assertEquals( $builder->bindings(), array(1, 39, 13));

        //$this->_print( $builder );
    }


    /**
     *
     */
    public function testBuildWhere() {
        $builder = new Nano_Db_Query_Builder();

        $builder->select( 'id', 'slug' )
        ->from( 'item' )
        ->where( array('id' => array('IN', range(40, 70) ) ) );

        //print $builder;

        //printf(">>\n%s\n", $builder );
        //
        //$builder->select( array('operator' => 'count') );
        //
        //printf("--\n%s\n", $builder );
        //
        //$builder->where( array('id' => array('<', 100 ) ) );
        //printf("--\n%s\n", $builder );
        //
        //
        //
        //$builder->select( array('operator' => 'max', 'column' => 'id', 'table' => 'item') );
        //printf("--\n%s\n", $builder );
        //
        //$builder->limit(10);
        //printf("--\n%s\n", $builder );
        //
        //$builder->limit(10, 5);
        //printf("--\n%s\n", $builder );
        //
        //$builder->offset(7);
        //$builder->groupBy( 'slug' );
        //
        //
        //printf("--\n%s\n", $builder );
        //
        //$builder = new Nano_Db_Query_Builder();
        //
        //$builder->delete()
        //    ->from('frobnitz')
        //    ->where(array('bla' => 1, 'biz' => 23 ));
        //
        //printf("--\n%s\n", $builder );
        //
        //

    }


}
