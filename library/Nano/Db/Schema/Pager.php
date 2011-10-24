<?php
error_reporting(E_ALL | E_STRICT);
/**
 * @file Nano/Db/Schema/Pager.php
 *
 * Wrapper around Nano_Db_Schema that handles query logic for paginated
 * results ( e.g. sets the offset/limits based on page number etc. )
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
 * @package    Nano_Db_Schema_Pager
 * @subpackage Nano_Db
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 */
class Nano_Db_Schema_Pager{
    private $_schema;
    private $_pager;

    private $_pageSize;
    private $_schemaArgs;

    public function __construct(Nano_Db_Schema $schema, array $schema_args = null, array $pgr_arguments = array() ){
        $this->_schema      = $schema;
        $this->_schemaArgs  = (array) $schema_args;

        $this->_pageSize = isset($pgr_arguments['page_size'])
            ? $pgr_arguments['page_size'] : Nano_Db_Schema_Mapper::FETCH_LIMIT;

    }

    public function __get( $name ){
        if( null == $this->_pager ){
            $this->_pager = $this->_build_pager();
        }

        return $this->_pager->$name;
    }

    public function setPage( $page_num ){
        $this->_pager = $this->_build_pager( null, null, $page_num );
    }

    public function getPage( $page_num = null ){
        if( $page_num ) $this->setPage($page_num);

        $schema_args = $this->_schemaArgs;

        $schema_args['limit'] = array(
            $this->offset,
            $this->pageSize
        );

        return $this->_schema->search( $schema_args );
    }

    private function _build_pager( $total = null, $page_size = null, $current_page = 1 ){
        if( null == $total ) $total = $this->_schema->count($this->_schemaArgs);
        if( null == $page_size ) $page_size = $this->_pageSize;

        return new Nano_Pager( $total, $page_size, $current_page );
    }



}
