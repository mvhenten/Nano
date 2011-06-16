<?php
/**
 * @file Nano/Db/Schema/Mapper.php
 *
 * Database mapper that uses Nano_Db_Schema to construct some query logic
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
 * @package    Nano_Db_Schema
 * @subpackage Nano_Db
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 */
class Nano_Db_Schema_Mapper{
    const FETCH_LIMIT = 50;
    const FETCH_OFFSET = 0;

    private $_limit     = self::FETCH_LIMIT;
    private $_offset    = self::FETCH_OFFSET;

    protected $_adapter = 'default';

    /**
     * Fetch object properties into the model
     *
     * @param Some_Model $model Model to fetch into
     * @return void
     */
    public function find( Nano_Db_Schema $schema, $id ){
        $where = $id;
        $key   = $schema->key();
        $klass = get_class( $schema );

        if( is_numeric( $id ) ){
            $where = array( $key => $id );
        }

        $result = $this->getAdapter()
            ->select( $schema->columns(), $schema->table(), $where, 1 )
            ->fetchObject( $klass );

        return $result;
    }

    /**
     * update or insert this model into the database
     * @param Nano_Db_Schema $schema
     */
    public function put( Nano_Db_Schema $schema, array $values=array() ){
        $key    = $schema->key();
        $values = empty($values) ? $schema->values() : $values;
        $where  = array( $key => $values[$key] );

        if( ! empty( $values ) ){
            if( isset( $values[$key] ) ){

                unset( $values[$key] );

                $this->getAdapter()
                    ->update( $schema->table(), $values, $where );
            }
            else{
                $id = $this->getAdapter()
                    ->insert( $schema->table(), $values );
                $schema->$key = $id;
            }
        }

        return $schema;
    }

    /**
     * Delete this object from the database using it's primary key
     * @param Some_Model $model Model to delete
     * @return void
     */
    public function delete( Nano_Db_Schema $schema ){
        $key    = $schema->key();
        $value  = $schema->$key;
        $where  = array( $key => $value );

        if( $key && $value ){
            $this->getAdapter()
                ->delete( $schema->table(), $where );
        }

        $schema = null;
    }


    /**
     * Perform a search based on the current model's properties
     * @todo implement a paged-query
     *
     */
    public function search( Nano_Db_Schema $schema, $arguments = array() ){
        $arguments = (array) $arguments;
        $limit = $this->_buildLimit( $arguments );
        $where = isset($arguments['where']) ? $arguments['where'] : array();

        $from = $schema->table();
        $what = $schema->columns();

        $sth = $this->getAdapter()
            ->select( $what, $from, $where, $limit );

        $sth->setFetchMode( PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, get_class( $schema ) );
        return $sth;
    }


    /**
     * Fetch default database adapter
     * @return Nano_Db_Adapter $adapter
     */
    protected function getAdapter(){
        return Nano_Db::getAdapter( $this->_adapter );
    }

    private function _buildLimit( $arguments ){
        $args = array( 0, $this->_limit );

        if( key_exists( 'limit', $arguments ) ){
            $args = (array) $arguments['limit'];
        }

        $args[] = 0;

        list( $offset, $limit ) = $args;
        return array( $offset, $limit );
    }

    private function _dasherize( $key ){
        preg_match_all('/[A-Z][^A-Z]*/',ucfirst($key),$results);
        $results = array_map( 'strtolower', $results[0] );
        return join( '_', $results );
    }
}
