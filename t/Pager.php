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
        $this->assertEquals( $this->_pager(98,8,13)->currentPageSize, 2);
    }

    public function testFirstPage(){
        $this->assertEquals( $this->_pager()->firstPage, 1 );
    }

    public function testLastPage(){
        $this->assertEquals( $this->_pager(120,10,1)->lastPage, 12 );
    }

    public function testFirst(){
        $this->assertEquals( $this->_pager()->first, 51 );
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

    public function testSkipped(){
        $this->assertEquals( $this->_pager()->skipped, 50 );
    }
}
