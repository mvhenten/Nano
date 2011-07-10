<?php
class Nano_Db_Pager{
    const _OFFSET_DEFAULT = 0;
    const _LIMIT_DEFAULT = 100;

    private $_sth;

    private $_offset;
    private $_limit;
    private $_count;
    private $_currentPageSize;

    public function __construct( PDOStatement $sth ){
        $this->_sth = $sth;
    }

    public function current(){
        return ceil( $this->offset() / $this->limit() );
    }

    public function currentPage(){
        return $this->current();
    }

    public function nextPage(){
        $offset = ($this->offset()+$this->limit());

        if( $offset < $this->count() ){
            return (int) ( $offset / $this->limit() );
        }

    }

    public function previousPage(){
        $offset = ($this->offset() - $this->limit());

        if( $offset > 0 ){
            return (int) ( $this->offset()/$this->limit());
        }
    }

    /**
     * Returns first page (1)
     */
    public function firstPage(){
        return 1;
    }

    /**
     * Returns the number of the 'last page' by doing count/limit
     *
     * @return int $last_page
     */
    public function lastPage(){
        return ceil( $this->count() / $this->limit() );
    }

    /**
     * page_size == limit
     *
     * @return int $limit
     */
    public function pageSize(){
        return $this->limit();
    }

    /**
     * Returns the size of CurrentPage ( == rowcount );
     *
     * @return int $current_page_size
     */
    public function currentPageSize(){
        return $this->_sth->rowCount();
    }

    /**
     * total pages for this rowset according to TOTAL/pageSize
     *
     * @return int $total_pages
     */
    public function totalPages(){
        return ceil( $this->count() / $this->pageSize() );
    }

    /**
     * return offset of current page, or offset of page $page_num
     *
     * @param integer $page_num (OPTIONAL) page number
     * @return int $offset
     */
    public function pageOffset( $page_num = null ){
        if( $page_num ){
            return $this->offset() * $page_num;
        }

        return $this->offset();
    }

    /**
     * total rowcount for this query
     *
     * @return int $row_count
     */
    public function count(){
        if( null == $this->_count ){
            $this->_count = $this->_getCount();
        }

        return $this->_count;
    }

    public function limit(){
        if( null == $this->_limit ){
            $this->_setOffsetLimit();
        }

        return $this->_limit;
    }

    public function offset(){
        if( null == $this->_offset ){
            $this->_setOffsetLimit();
        }

        return $this->_offset;
    }

    private function _setOffsetLimit(){
        list( $offset, $limit ) = $this->_getOffsetLimit();
        $this->_offset = $offset;
        $this->_limit = $limit;
    }

    private function _getOffsetLimit(){
        $sql = $this->_sth->queryString;
        $offset = self::_OFFSET_DEFAULT;
        $limit  = self::_LIMIT_DEFAULT;

        if( preg_match( '/LIMIT\s+(\d+)\s+OFFSET\s+(\d+)?/', $str, $matches) ){
            $match = array_shift( $matches );

            list( $limit, $offset ) = $matches;
        }
        else if( preg_match( '/LIMIT\s(\d+),\s*(\d)?/i', $sql, $matches) ){
            $match = array_shift( $matches );

            list( $offset, $limit ) = $matches;
        }

        return array( $offset, $limit );
    }

    private function _getCount(){
        $sql = $this->_sth->queryString;
        $sql = 'SELECT COUNT(1) ' . stristr( $sql, 'from' );

        try{
            var_dump($sql);
            $dbh = Nano_Db::getAdapter();
            $sth = $dbh->query( $sql );

            //var_dump( $dbh->errorInfo() );

            $count = $sth->fetch(0);

            //var_dump($sql);
        }
        catch( Exception $e ){
            throw new Exception( sprintf('FAIL: %s mumbl mumbl', $sql ) );
        }

        return $count;
    }



}
