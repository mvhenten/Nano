<?php
/**
 * library/Nano/Db/Schema/Mapper.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


error_reporting(E_ALL | E_STRICT);

if ( ! defined( 'NANO_DEBUG' ) ) {
    define( 'NANO_DEBUG', false );
}

/**
 *
 *
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
class Nano_Db_Schema_Mapper {
    const FETCH_LIMIT = 100;
    const FETCH_OFFSET = 0;

    private $_limit     = self::FETCH_LIMIT;
    private $_offset    = self::FETCH_OFFSET;

    protected $_adapter = 'default';
    protected $_last_arguments = null;
    private $_builder;


    /**
     * Fetch object properties into the model using a primary key
     * This is a shortcut for "find" with fetchmode set to PDO::FETCH_INTO
     *
     * @param mixed   id Values for primary key ( single value or key => value )
     *
     * @param object  $schema The schema to fetch into
     * @param unknown $id
     * @return Nano_Db_Schema $schema Schema, on success.
     */
    public function load( Nano_Db_Schema $schema, $id ) {
        return $this->find( $schema, $id, PDO::FETCH_INTO );
    }


    /**
     * Fetch result for schema using a primary key
     *
     * @param mixed   id Values for primary key ( single value or key => value )
     *
     * @param object  $schema    The schema to fetch "as"
     * @param unknown $id
     * @param unknown $fetchMode (optional)
     * @return Nano_Db_Schema $schema Schema, on success.
     */
    public function find( Nano_Db_Schema $schema, $id, $fetchMode = PDO::FETCH_CLASS ) {
        $keys   = (array) $schema->key();
        $id     = (array) $id;
        $where  = array();

        if ( count($keys) == 1 ) {
            $where = array_combine( $keys, $id );
        }
        else {
            $where = array_filter(array_intersect_key( array_flip($keys), $id ));

            if ( count($where) != count($keys) ) {
                return;
            }
        }

        $builder = $this->_builder()
        ->select( $schema->columns() )
        ->from( $schema->table() )
        ->where( $where )
        ->limit(1);

        $sth = $this->_saveExecute( $builder->sql(), $builder->bindings() );

        if ( $fetchMode == PDO::FETCH_INTO ) {
            $sth->setFetchMode( PDO::FETCH_INTO, $schema );
        }
        else {
            $sth->setFetchMode( PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE,
                get_class( $schema ) );
        }

        return $sth->fetch();
    }


    /**
     * Performs a simple select query
     *
     *
     * @param object  $schema    The schema to fetch "as"
     * @param array   $arguments (optional) Optional array array( 'where' =>, 'limit' => )
     * @return PdoStatement $sth
     */
    public function search( Nano_Db_Schema $schema, array $arguments = array() ) {
        $arguments = (array) $arguments;

        list( $offset, $limit ) = $this->_buildLimit( $arguments );

        $where   = isset($arguments['where']) ? $arguments['where'] : array();
        $columns = isset($arguments['columns']) ? $arguments['columns'] : $schema->columns();

        $builder = $this->_builder()->select( $columns )
        ->from( $schema->table() )
        ->where( $where )
        ->limit( $limit, $offset );


        if ( isset($arguments['group']) ) {
            $builder->group( $arguments['group']);
        }

        if ( isset($arguments['order']) ) {
            $builder->order( $this->_buildOrderClause( $schema, $arguments['order'] ) );
        }

        $sth = $this->_saveExecute( $builder->sql(), $builder->bindings() );
        $sth->setFetchMode( PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, get_class( $schema ) );
        return $sth;
    }


    /**
     * Performs a simple count query. Note that LIMIT is not added to the query
     *
     *
     * @param object  $schema    The schema to fetch "as"
     * @param array   $arguments (optional) Optional array array( 'where' => )
     * @return PdoStatement $sth
     */
    public function count( Nano_Db_Schema $schema, $arguments = array() ) {
        $arguments = (array) $arguments;

        $where = isset($arguments['where']) ? $arguments['where'] : array();

        $builder = $this->_builder()->select(array( 'count' => $schema->key() ))
        ->from( $schema->table() )
        ->where( $where );

        $sth = $this->_saveExecute( $builder->sql(), $builder->bindings() );
        $sth->setFetchMode( PDO::FETCH_COLUMN , 0 );

        return $sth->fetch();
    }


    /**
     * Performs a many_to_many query
     *
     * @FIXME @TODO implement this trough builder
     *
     *
     * @param object  $schema    The schema to fetch "as"
     * @param array   $arguments (optional) Optional array array( 'where' =>, 'limit' => )
     * @return PdoStatement $sth
     */
    public function many_to_many( Nano_Db_Schema $schema, $arguments = array() ) {
        $arguments = (array) $arguments;
        $select = array();

        list( $offset, $limit ) = $this->_buildLimit( $arguments );

        $where   = isset($arguments['where']) ? $arguments['where'] : array();
        $columns = isset($arguments['columns']) ? $arguments['columns'] : $schema->columns();

        $join_clause = reset($arguments['join']);
        $join_table  = key($arguments['join']);
        $values = array( reset($where) );

        foreach ( $columns as $column ) {
            $select[] = array( 'table' => $schema->table(), 'columns' => $column );
        }

        $query = $this->_builder()->select( $columns )
        ->from( $schema->table() )->sql();

        //@FIXME Builder should support left_join
        // so this printing of SQL is not needed
        $query .= sprintf('
            LEFT JOIN `%s` %s ON %s.`%s` = %s.`%s`
            WHERE %s.`%s` = ?
        ',
            $join_table, 'b',
            'b', reset($join_clause), //foreign key
            'a', key($join_clause),   // key of $schema->table
            'b', key($where)          // $where clause is
        );

        //@FIXME. Use Builder... this is a quick hack...
        if ( isset($arguments['order']) ) {
            $order_column = $arguments['order'];
            $order_table = 'a';

            //@FIXME Another quick hack for array( 'tablename' => 'order_col' );
            if ( is_array( $order_column ) ) {
                $order_table  = isset( $order_column[$join_table] ) ? 'b' : 'a';
                $order_column = reset($order_column);
            }

            $query .= sprintf(' ORDER BY %s.`%s`', $order_table, $order_column );
        }

        //@FIXME Use Builder. this is a hack.
        if ( $limit ) {
            $query = sprintf("%s\nLIMIT %s, %s", $query, intval($offset), intval($limit) );
        }

        $sth = $this->_saveExecute( $query, array(reset($where)) );
        $sth->setFetchMode( PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE,
            get_class( $schema ) );

        return $sth;
    }


    /**
     * Performs a simple delete query, either by using $values based on primary
     * key, or an optional extra $where
     *
     *
     * @param object  $schema The schema to fetch "as"
     * @param array   $where  (optional) Optional, if omitted, the schema's primary key will be attempted
     * @return PdoStatement $sth
     */
    public function delete( Nano_Db_Schema $schema, array $where = array() ) {
        if ( count($where) == 0 ) {
            $where = array_flip( (array) $schema->key() );
            $where = array_intersect_key( $schema->values(), $where );
            $where = array_filter( $where );

            if ( count($where) != count($schema->key()) ) {
                return false;
            }
        }

        $builder = $this->_builder()
        ->delete( $schema->table() )
        ->where( $where );

        return $this->_saveExecute( $builder->sql(), $builder->bindings() );
    }


    /**
     * Update or insert this model into the database, depending on the
     * availability of $primary_key
     *
     * @param Nano_Db_Schema $schema
     * @param unknown $where  (optional)
     * @return PdoStatement $sth Statement when updating, last-insert id otherwise
     */
    public function store( Nano_Db_Schema $schema, $where = array() ) {
        if ( is_array( $where ) && count( $where ) > 0 ) {
            return $this->_update( $schema, $schema->values(), $where );
        }
        else {
            return $this->_insert( $schema, $schema->values() );
        }
    }


    /**
     * Insert this model into the database. This function does not check
     * the validity of $values, it just attempts to insert them.
     *
     * @param Nano_Db_Schema $schema
     * @param array   $values
     * @return int $id Last-insert id
     */
    private function _insert( Nano_Db_Schema $schema, array $values ) {
        $builder = $this->_builder()
        ->insert( $schema->table(), $schema->values() );

        $sth = $this->_saveExecute( $builder->sql(), $builder->bindings() );
        return $this->getAdapter()->lastInsertId();
    }


    /**
     * Update this model depending on a $where. This function does not check
     * the validity of $values, it just attempts to update them.
     *
     * @param Nano_Db_Schema $schema
     * @param array   $values
     * @param array   $where
     * @return int $id Last-insert id
     */
    private function _update( Nano_Db_Schema $schema, array $values, array $where ) {
        //$where = array_intersect_key( $values, array_flip($keys) );
        $values = array_diff_key($values, $where);

        $builder = $this->_builder()
        ->update( $schema->table(), $values )
        ->where( $where );

        return $this->_saveExecute( $builder->sql(), $builder->bindings() );
    }


    /**
     * Perform a SQL query $sql
     * This function does not check the validity of your sql, nor $values
     *
     * @param string  $sql
     * @param mixed   $values
     * @return PdoStatement $sth PDO statement on success
     */
    public function query( $sql, $values ) {
        return $this->_saveExecute( $sql, $values );
    }


    /**
     * Fetch default database adapter
     *
     * @return Nano_Db_Adapter $adapter
     */
    protected function getAdapter() {
        return Nano_Db::getAdapter( $this->_adapter );
    }



    /**
     * If it's an array and it doesn't look like a proper order clause,
     * assume we want 'order' => array($cols) to become a proper order clause.
     *
     * @param object  $schema
     * @param string  $order_arguments
     * @return array $order_clause
     */
    private function _buildOrderClause( Nano_Db_Schema $schema, $order_arguments ) {
        $order_clause = $order_arguments;

        if ( !isset( $order['table'] ) ) {
            $order_clause = array(
                'table' => $schema->table(),
                'col'   => $order_arguments,
            );
        }

        return $order_clause;
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
     * @param unknown $arguments
     * @return unknown
     */
    private function _buildLimit( array $arguments ) {
        list( $offset, $limit ) = array( 0, $this->_limit );

        if ( isset( $arguments['limit'] ) ) {
            $limit_args = (array) $arguments['limit'];
            if ( count( $limit_args ) > 1 ) {
                @list( $offset, $limit ) = $limit_args;
            }
            else {
                $limit = $limit_args[0];
            }
        }

        return array( $offset, $limit );
    }


    /**
     *
     *
     * @param unknown $query
     * @param array   $values
     * @return unknown
     */
    private function _saveExecute( $query, array $values ) {
        $sth = $this->getAdapter()->prepare( $query );

        if ( false == $sth ) {
            $error = print_r( $this->getAdapter()->errorInfo(), true );

            if ( NANO_DEBUG ) {
                print( $query );
            }

            throw new Exception( 'Query failed: PDOStatement::errorCode():' . $error );
        }
        else {
            $bindings = $values;
            $success = (bool) $sth->execute( (array) $values );

            if ( ! $success ) {
                $error = print_r( $sth->errorInfo(), true );

                if ( NANO_DEBUG  ) {
                    print( $error );
                    print( $query );
                }

                throw new Exception( 'Query failed: PDOStatement::errorCode():' . $error );
            }
        }

        return $sth;
    }


    /**
     *
     *
     * @param unknown $key
     * @return unknown
     */
    private function _dasherize( $key ) {
        preg_match_all('/[A-Z][^A-Z]*/', ucfirst($key), $results);
        $results = array_map( 'strtolower', $results[0] );
        return join( '_', $results );
    }


}
