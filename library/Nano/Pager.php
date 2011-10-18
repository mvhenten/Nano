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
 * Helper for paging through sets of results
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
    private $_skipped           = null;

    public function __construct( $total, $page_size, $current_page = 1 ){
        if( $current_page < 0 ){
            throw new Exception( 'Current page may not be 0' );
        }

        $this->_total = $total;
        $this->_pageSize = $page_size;

        $this->_currentPage = min( $this->lastPage, $current_page );
    }

    public function __get( $name ){
        if( ( $property = "_$name" ) && property_exists( $this, $property ) ){
            if( ! isset( $this->$property ) ){
                if( ( $method = "_build$property" ) && method_exists( $this, $method ) ){
                    $this->$property = $this->$method();
                }
            }

            return $this->$property;
        }
    }

    private function _build_currentPageSize(){
        $current_page_size = $this->_total - ( $this->_pageSize * ($this->_currentPage-1) );
        return $current_page_size;
    }

    private function _build_lastPage(){
        return ceil( $this->_total / $this->_pageSize );
    }

    private function _build_first(){
        return ( $this->_currentPage * $this->_pageSize ) + 1;
    }

    private function _build_last(){
        $last = ( ($this->_currentPage + 1) * $this->_pageSize );
        return min( $this->_total, $last );
    }

    private function _build_previousPage(){
        if( $this->_currentPage > 1 ){
            return $this->_currentPage - 1;
        }
    }

    private function _build_nextPage(){
        if( $this->_currentPage < $this->lastPage ){
            return $this->_currentPage + 1;
        }
    }

    private function _build_skipped(){
        return $this->first - 1;
    }
}
