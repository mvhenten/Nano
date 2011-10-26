<?php
/**
 * @file Nano/Pager.php
 *
 * Complete ripoff from Data::Page - help when paging through sets of results
 * @see http://search.cpan.org/~lbrocard/Data-Page-2.02/lib/Data/Page.pm
 *
 * Copyright (C) <2011>  <Matthijs van Henten>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @category   Nano
 * @package    Nano_Gd
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 */
/**
 * @class Nano_Pager
 * Helper for paging through sets of results - the calculations are trivial
 */
class Nano_Pager {

    private $_total             = null;
    private $_pageSize          = null;
    private $_currentPage       = null;
    private $_currentPageSize   = null;
    private $_firstPage         = 1;
    private $_lastPage          = null;
    private $_first             = null;
    private $_last              = null;
    private $_previousPage      = null;
    private $_nextPage          = null;
    private $_offset            = null;

    /**
     * Create a new Pager
     *
     * @param int $total          Total elements in this set
     * @param int $page_size Page Max number of elements per page
     * @param int $current_page   Current page, must be > 0, will be capped to lastPage
     */
    public function __construct( $total, $page_size, $current_page = 1 ){
        if( $current_page < 0 ){
            throw new Exception( 'Current page may not be < 1' );
        }

        $this->_total = $total;
        $this->_pageSize = $page_size;

        $this->_currentPage = min( $this->lastPage, $current_page );
    }

    /**
     * Proxies private methods to builders if value is not set
     */
    public function __get( $name ){
        if( ( $property = "_$name" ) && property_exists( $this, $property ) ){
            if( ! isset( $this->$property ) ){
                if( ( $method = "_build$property" ) && method_exists( $this, $method ) ){
                    $this->$property = (int) $this->$method();
                }
            }

            return $this->$property;
        }
    }

    /**
     * Returns a number of pages in a range starting from the current page up to
     * the last defined by $max_size
     */
    public function range( $max_size = 12, $offset = 6 ){
        $range = array();

        if( $this->total > 1 ){
            $offset = $offset >= $max_size ? intval($max_size/2) : $offset;

            $range_start = max( $this->firstPage, ($this->currentPage - $offset) );
            $range_end   = min( $range_start + $max_size, $this->lastPage );

            $range = range( $range_start, $range_end );
        }

        return $range;
    }

    /**
     * Number of elements on the current page
     *
     * @return int $currentPageSize
     */
    private function _build_currentPageSize(){
        $current_page_size = $this->_total - ( $this->_pageSize * ($this->_currentPage-1) );
        return $current_page_size;
    }

    /**
     * Last page in this set ( e.g. total pages! )
     *
     * @return int $lastPage
     */
    private function _build_lastPage(){
        return ceil( $this->_total / $this->_pageSize ) - 1;
    }

    /**
     * First number on the current page
     *
     * @return int $first
     */
    private function _build_first(){
        return max(0, 1 + (( $this->_currentPage * $this->_pageSize ) - $this->_pageSize));
    }

    /**
     * Last number on the current page
     *
     * @return int $last
     */
    private function _build_last(){
        $last = ( ($this->_currentPage + 1) * $this->_pageSize );
        return min( $this->_total, $last );
    }

    /**
     * Previous page in this set or null
     *
     * @return int $previousPage
     */
    private function _build_previousPage(){
        if( $this->_currentPage > 1 ){
            return $this->_currentPage - 1;
        }
    }

    /**
     * Next page in this set or null
     *
     * @return int $nextPage
     */
    private function _build_nextPage(){
        if( $this->_currentPage < $this->lastPage ){
            return $this->_currentPage + 1;
        }
    }

    /**
     * Returns offset for sql queries
     *
     * @return int $offset
     */
    private function _build_offset(){
        return max(0, $this->first - 1);
    }
}
