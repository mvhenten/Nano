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
    private $_builder;


    /**
     * update or insert this model into the database
     * @param Nano_Db_Schema $schema
     */
    public function put( Nano_Db_Schema $schema, $values = null ){
        $primaryKey  = $schema->key();

        if( is_array( $values ) ){
            foreach( $values as $key => $value ){
                $schema->$key = $value;
            }
        }

        if( null == $schema->$primaryKey ){
            $this->_insert( $schema );
        }
        else{
            $this->_update( $schema );
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
        list( $offset, $limit ) = $this->_buildLimit( $arguments );

        $where = isset($arguments['where']) ? $arguments['where'] : array();

        $builder = $this->_builder()->select( $schema->columns() )
            ->from( $schema->table() )
            ->where( $where )
            ->limit( $limit, $offset );


        $sth = $this->_saveExecute( (string) $builder, $builder->bindings() );
        $sth->setFetchMode( PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, get_class( $schema ) );
        return $sth;
    }

    /**
     * Fetch object properties into the model
     *
     * @param Some_Model $model Model to fetch into
     * @return void
     */
    public function find( Nano_Db_Schema $schema, $id, $fetchMode = PDO::FETCH_CLASS ){
        $where = $id;
        $key   = $schema->key();

        $builder = $this->_builder()
            ->select( $schema->columns() )
            ->from( $schema->table() )
            ->where( array( $key => $id ) );

        $sth = $this->_saveExecute( (string) $builder, $builder->bindings() );

        if( $fetchMode == PDO::FETCH_INTO ){
            $sth->setFetchMode( PDO::FETCH_INTO, $schema );
        }
        else{
            $sth->setFetchMode( PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE,
                get_class( $schema ) );

        }

        return $sth->fetch();
    }

    public function load( Nano_Db_Schema $schema, $id ){
        return $this->find( $schema, $id, PDO::FETCH_INTO );
    }


    public function pager( Nano_Db_Schema $schema, $arguments = array() ){}

    private function _insert( Nano_Db_Schema $schema ){
        $table   = $schema->table();
        $key     = $schema->key();
        $columns = $schema->columns();

        $values = array();

        foreach( $columns as $col ){
            if( $key == $col ) continue;
            $values["`$col`"] = $schema->$col;
        }

        $query = sprintf('
            INSERT INTO `%s`
            ( %s )
            VALUES ( %s )
        ', $table,
            join(',', array_keys($values)),
            join( ',', array_fill(0, count($values), '?'))
        );

        $this->_saveExecute( $query, $values );
        $this->load( $schema, $this->getAdapter()->lastInsertId() );

        return $schema;
    }

    public function execute( Nano_Db_Schema $schema, $sql, $bindings, $fetchMode = null){
        $sth = $this->_saveExecute( (string) $sql, $bindings );

        if( $fetchMode == PDO::FETCH_INTO ){
            $sth->setFetchMode( PDO::FETCH_INTO, $schema );
        }
        else{
            $sth->setFetchMode( PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE,
                get_class( $schema ) );
        }

        return $sth;
    }

    private function _update( $table, array $fields, array $where ){
        $table   = $schema->table();
        $key     = $schema->key();
        $columns = $schema->columns();

        $values = array();

        foreach( $columns as $col ){
            if( $key == $col ) continue;
            $values["`$col` = ?"] = $schema->$col;
        }

        $query = sprintf('
            UPDATE `%s`
            SET %s
            WHERE `%s` = ?
        ', $table,
            join(',', array_keys($values)),
            $schema->key()
        );

        $values[] = $schema->$key;

        $this->_saveExecute( $query, $values );
        $this->load( $schema, $this->getAdapter()->lastInsertId() );

        return $schema;
    }

    private function _saveExecute( $query, $values ){
        $sth = $this->getAdapter()->prepare( $query );

       if( false == $sth ){
            $error = print_r( $this->getAdapter()->errorInfo(), true );
            throw new Exception( 'Query failed: PDOStatement::errorCode():' . $error );
        }
        else if( !$sth->execute( array_values($values) ) ){
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

    private function _builder(){
        if( null == $this->_builder ){
            $this->_builder = new Nano_Db_Query_Builder();
        }

        return $this->_builder;
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
