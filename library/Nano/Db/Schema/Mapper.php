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
    protected $_last_arguments = null;


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

                $this->_update( $schema->table(), $values, $where );
            }
            else{
                $id = $this->_insert( $schema->table(), $values );
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
            $this->_delete( $schema->table(), $where );
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

        $sth = $this->_select( $what, $from, $where, $limit );

        $sth->setFetchMode( PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, get_class( $schema ) );
        return $sth;
    }

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

        $sth = $this->_select( $schema->columns(), $schema->table(), $where, 1 );

        $sth->setFetchMode( PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, get_class( $schema ) );

        return $sth->fetch();
    }


    public function pager( Nano_Db_Schema $schema, $arguments = array() ){
        /**
         * @todo implement a pager that can fallback on the arguments
         * provided by search. Current argumetns may override thosee.
         * Should return a Nano_Db_Pager class that does the grunt work of
         * handligin the SQL
         *
         */
    }

    private function _insert( $table, array $values ){
        $keys   = array();

        $columns = array_map(
            'sprintf',
            array_fill(0, count($values), '`%s`'),
            array_keys( $values )
        );

        $keys = array_map(
            'sprintf',
            array_fill(0, count($values), ':%s'),
            array_keys($values)
        );

        $values = array_combine( $keys, $values );

        $query = array();
        $query[] = 'INSERT INTO';
        $query[] = '`' . $table . '`';
        $query[] = '(' . join(',', $columns ) . ')';
        $query[] = 'VALUES';
        $query[] = '(' . join(',', $keys ) . ')';

        $sth = $this->prepare( join("\n", $query ) );
        $this->_saveExecute( $query, $values );

        return $this->lastInsertId();
    }

    private function _update( $table, array $fields, array $where ){
        $field_values = array();

        foreach( $fields as $key => $value ){
            $query_set = sprintf( '`%s` = ?', $key );
        }

        list( $where_key, $where_value ) = $where;

        $query_values = array_values( $fields );
        $query_values[] = $where_value;

        $sql = sprintf('
            UPDATE `%s`
            SET
                %s
            WHERE `%s` = :key
        ', $table,
            join( "", $query_set ),
            $where_key
        );

        $sth = $this->prepare( $sql );
        $this->_saveExecute( $query, $values );

        return $this->lastInsertId();
    }

    private function _select( array $what = array(), $from, array $where = array(), $limit = null ){
        $select_columns = $this->_buildSelectColumns( $what, $from );
        $limit_clause   = $this->_buildLimitClause( $limit );

        list( $where_clause, $values ) = $this->_buildWhereClause( $where, $from );

        $query = sprintf('SELECT %s FROM `%s`', join( ",\n", $select_columns ), $from );

        if( ! empty( $where_clause ) ){
            $query .= "\nWHERE " . join( "AND", $where_clause );
        }

        if( ! empty( $limit_clause ) ){
            list( $offset, $limit ) = $limit_clause;
            $query .= sprintf("\nLIMIT %d OFFSET %d", $limit, $offset );
        }

        return $this->_saveExecute( $query, $values );
    }

    private function _delete( $from, array $where ){
        list( $where_clause, $values ) = $this->_buildWhereClause( $where, $from );

        $query = sprintf('DELETE FROM `%s` WHERE %s', $from, join( "AND", $where_clause ) );
        return $this->_saveExecute( $query, $values );
    }


    private function _buildLimitClause( $limit ){
        $limit_clause = array();

        if( ! empty($limit) ){
            $limit_clause = (array) $limit;
            if( count($limit_clause) == 1 ){
                array_unshift($limit_clause, 0);
            }
        }

        return $limit_clause;
    }

    private function _buildSelectColumns( $what, $from ){
        $what = !empty($what) ? $what : array('*');
        $select_columns = array();

        foreach( $what as $column ){
            $select_columns[] = sprintf('`%s`.`%s`', $from, $column );
        }

        return $select_columns;
    }

    private function _buildWhereClause( $where, $from ){
        $where_clause = array();
        $where_values = array();

        foreach( $where as $key => $value ){
            $op = '=';

            if( is_array( $value ) ){
                list( $op, $nvalue ) = $value;
                $value = $nvalue;
            }

            if( $op == 'IN' && is_array($value) ){
                $where_in = array_fill( 0, count($value), '?' );

                $where_clause[] = sprintf('`%s`.`%s`  IN (%s)', $from, $key, join(',', $where_in));
                $where_values = array_merge( $where_values, $value );
            }
            else{
                if( ! in_array( $op, array( 'LIKE', '>', '<', '=', '!=', 'NOT' ) ) ){
                    throw new Exception( 'Unsupported operator: ' . $op );
                }

                $where_clause[] = sprintf( '`%s`.`%s` %s ?', $from, $key, $op );
                $where_values[] = $value;
            }
        }

        return array( $where_clause, $where_values );
    }

    private function _saveExecute( $query, $values ){
        $sth = $this->getAdapter()->prepare( $query );

       if( false == $sth ){
            $error = print_r( $this->errorInfo(), true );
            throw new Exception( 'Query failed: PDOStatement::errorCode():' . $error );
        }
        else if( !$sth->execute( $values ) ){
            $error = print_r( $sth->errorInfo(), true );
            throw new Exception( 'Query failed: PDOStatement::errorCode():' . $error );
        }

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
