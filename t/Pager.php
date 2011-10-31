<?php
class Nano_PagerTest extends PHPUnit_Framework_TestCase{
    private $config;

    protected function setUp(){
        require_once( dirname(dirname(__FILE__)) . '/library/Nano/Autoloader.php');
        Nano_Autoloader::register();
    }

    private function _pager( $total = 100, $page_size = 10, $current_page = 5 ){
        return new Nano_Pager( $total, $page_size, $current_page );
    }

    public function testTotal(){
        $this->assertEquals( $this->_pager()->total, 100 );
    }

    public function testPageSize(){
        $this->assertEquals( $this->_pager()->pageSize, 10 );
    }

    public function testCurrentPage(){
        $this->assertEquals( $this->_pager()->currentPage, 5 );
    }

    public function testCurrentPageSize(){
        //var_dump( $this->_pager(12,8,2)->currentPageSize );
        //$this->assertEquals(1,1);
        $this->assertEquals( $this->_pager(12,8,2)->currentPageSize, 4);
    }

    public function testFirstPage(){
        $this->assertEquals( $this->_pager()->firstPage, 1 );
    }

    public function testLastPage(){
        $this->assertEquals( $this->_pager(120,10,1)->lastPage, 12 );
    }

    public function testFirst(){
        $this->assertEquals( $this->_pager()->first, 41 );
    }

    public function testLast(){
        $this->assertEquals( $this->_pager()->last, 60 );
    }

    public function testPreviousPage(){
        $this->assertEquals( $this->_pager()->previousPage, 4 );
    }

    public function testNextPage(){
        $this->assertEquals( $this->_pager()->nextPage, 6 );
    }

    public function testOffset(){
        $this->assertEquals( $this->_pager()->offset, 40 );
    }

    public function testRange(){
        //100, 10, 5
        // walk the pager, check if $range is alwyas the same
        foreach( range(1, 10) as $page_nr ){
            $range = $this->_pager( 100, 10, $page_nr )->range(5);

            $this->assertEquals( 5, count($range) );
        }

    }
}
