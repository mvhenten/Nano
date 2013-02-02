<?php
/**
 * library/Nano/Db/Schema.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


error_reporting(E_ALL | E_STRICT);

abstract class Nano_Db_Schema {
    protected $_tableName   = null;
    protected $_schema      = null;
    protected $_primary_key = array();
    protected $_columns     = null;

    private $_values      = array();

    /**
     *
     */
    public final function __construct( $id = null ) {
        if ( is_numeric( $id )
            && ( 'int' == substr( $this->columnType( $this->key() ), 0, 3))) {
            $this->load( $id );
        }
        else if ( is_array( $id ) ) {
                $this->setValues( $id );
            }
    }


    /**
     *
     */
    public final function __get( $name ) {
        if ( in_array( $name, $this->columns() ) ) {
            if ( method_exists( $this, $name ) ) {
                return $this->$name();
            }
            return $this->get( $name );
        }
    }


    /**
     *
     */
    public final function get( $name ) {
        if ( isset( $this->_values[$name] ) ) {
            return $this->_values[$name];
        }
    }


    /**
     *
     */
    public final function __set( $name, $value ) {
        if ( in_array( $name, $this->columns() ) ) {
            $this->_values[$name] = $value;
        }
    }


    /**
     *
     */
    public final function __call( $method, $args ) {
        if ( method_exists( 'Nano_Db_Schema_Mapper', $method ) ) {
            return $this->_getMapper()->$method( $this, current($args) );
        }
        else {
            throw new Exception( "$method is not supported" );
        }
    }


    /**
     *
     *
     * @return unknown
     */
    public function schema() {
        return $this->_schema;
    }


    /**
     * Return the primary key name for this schema.
     *
     * @todo this only works for single column keys
     * @return unknown
     */
    public function key() {
        if ( count( $this->_primary_key ) == 1 ) {
            return $this->_primary_key[0];
        }
        return $this->_primary_key;
    }


    /**
     *
     *
     * @return unknown
     */
    public function columns() {
        if ( null == $this->_columns ) {
            $this->_columns = array_keys( $this->schema() );
        }

        return $this->_columns;
    }


    /**
     *
     *
     * @param unknown $column
     * @return unknown
     */
    public function columnType( $column ) {
        if ( in_array( $column, $this->columns() ) ) {
            return $this->_schema[$column]['type'];
        }
    }


    /**
     *
     *
     * @return unknown
     */
    public function values() {
        $values = array();
        foreach ( $this->_values as $key => $value ) {
            $values[$key] = $this->filter( $key, $value );
        }

        return array_filter( $values, 'is_scalar' );
    }


    /**
     *
     *
     * @param unknown $name
     * @param unknown $value
     * @return unknown
     */
    public function filter( $name, $value ) {
        if ( ( $method = '_filter_' . $name ) && method_exists( $this, $method ) ) {
            return $this->$method( $value );
        }
        return $value;
    }


    /**
     *
     *
     * @param array   $values
     */
    public function setValues( array $values ) {
        foreach ( $values as $key => $value ) {
            $this->__set( $key, $value );
        }
    }


    /**
     *
     *
     * @return unknown
     */
    public function table() {
        return $this->_tableName;
    }


    /**
     * Create a Nano_Db_Schema_Pager object
     *
     * @param string  $action          (optional) Action to perform on $this (defaults to 'search')
     * @param array   $arguments       (optional) Optional arguments for $action
     * @param array   $pager_arguments (optional) Optional arguments for the Nano_Pager object
     * @return void
     */
    public function pager( $action = 'search', array $arguments = array(), $pager_arguments = array() ) {
        if ( ! is_string( $action ) ) {
            throw new Exception( "action must be a string");
        }
        return new Nano_Db_Schema_Pager( $this, $action, $arguments, $pager_arguments );
    }


    /**
     * Wrapper around Nano_Db_Mapper::store. Store returns the last_insert_id,
     * but here we return the schema itself instead. On insert, the auto-increment
     * value of the originating schema is set.
     *
     * @param unknown $where (optional) A where clause: you are responsible of telling to update or insert
     * @return Nano_Db_Schema $original
     */
    public function store( $where = array() ) {
        $last_insert_id = $this->_getMapper()->store( $this, $where );

        if ( is_numeric( $last_insert_id ) ) {
            $this->_set_auto_increment( intval($last_insert_id) );
        }

        return $this;
    }


    /**
     * Wrapper around Nano_Db_Mapper::search, but adding sugar so you can
     * leave out the "where" key ( it will be added )
     *
     * @param array   $arguments (optional) Key value pairs like 'group', 'limit', 'where'
     * @return Nano_Db_Mapper results
     */
    public function search( array $arguments = array() ) {
        $arguments = $this->_prepare_arguments( $arguments );
        return $this->_getMapper()->search( $this, $arguments );
    }


    /**
     * Wrapper around Nano_Db_Mapper::count, adding sugar trough _prepare_arguments
     *
     * @param array   $arguments (optional) Key value pairs like 'group', 'limit', 'where'
     * @return Nano_Db_Mapper results
     */
    public function count( array $arguments = array() ) {
        $arguments = $this->_prepare_arguments( $arguments );
        return $this->_getMapper()->count( $this, $arguments );
    }


    /**
     * Wrapper around Nano_Db_Mapper::delete, adding sugar.
     *
     * @param array   $where (optional) Where for delete.
     * @return Nano_Db_Mapper results
     */
    public function delete( array $where = array() ) {
        return $this->_getMapper()->delete( $this, $where );
    }


    /**
     * Defines a 'has_one' unique relation established trough $schema
     *
     * @param string  $schema  Name of the schema class that we have one
     * @param array   $mapping $foreign_key => $key relation (as in $schema->$foreign_key)
     * @return unknown
     */
    protected function has_one( $schema, array $mapping ) {
        $key         = reset( $mapping );
        $foreign_key = key( $mapping );


        $sth = $this->_getMapper()->search( new $schema(), array(
                'where' => array( $foreign_key => $this->$key ),
                'limit' => 1
            ));

        return $sth->fetch();
    }


    /**
     * Defines a 'has_many' relation established trough $schema
     *
     * @param string  $schema    Name of the schema class that we have many of
     * @param array   $mapping   $foreign_key => $key relation (as in $schema->$foreign_key)
     * @param array   $arguments (optional)
     * @return unknown
     */
    protected function has_many( $schema, array $mapping, array $arguments = array() ) {
        $key         = reset( $mapping );
        $foreign_key = key( $mapping );

        $arguments = array_merge( $arguments, array(
                'where' => array( $foreign_key => $this->$key )));

        return $this->_getMapper()->search( new $schema(), $arguments);
    }


    /**
     * Defines a 'many_to_many' relation established trough $schema
     *
     * @param string  $relation  Name of the 'belongs_to' function in $schema
     * @param string  $schema    Name of the schema class that has the belongs_to
     * @param array   $mapping   $foreign_key => $key relation (as in $schema->$foreign_key)
     * @param array   $arguments (optional) order => $colname ( only order/limit supported now!);
     * @return unknown
     */
    protected function has_many_to_many( $relation, $schema, $mapping, $arguments = array() ) {
        $schema = new $schema();

        $key         = reset( $mapping );
        $foreign_key = key( $mapping );

        list($relation, $mapping ) = $schema->$relation();

        $arguments = array_merge( array(
                'join'  => array( $schema->table() => $mapping ),
                'where' => array( $foreign_key => $this->$key ),
            ), $arguments );

        return $this->_getMapper()->many_to_many( new $relation(), $arguments );
    }


    /**
     * Defines a 'belongs_to' relation for use in many_to_may relations
     *
     * @param string  $schema  Name of the schema class this table belongs to
     * @param array   $mapping $foreign_key => $key relation (as in $schema->$foreign_key)
     * @return unknown
     */
    protected function belongs_to( $schema, array $mapping ) {
        return array( new $schema(), $mapping );
    }


    /**
     * Parse function arguments to allow a little sugar:
     * Allow arguments for the where class to be passed in the root array,
     * instead of "where" => array()
     *
     * @param array   $arguments (optional) Arguments array from the original function
     * @return array $arguments Cleaned up version
     */
    private function _prepare_arguments( array $arguments = array() ) {
        $whitelist = array_flip(explode('|', 'where|limit|group|join|order|columns' ));

        $where = array_diff_key( $arguments, $whitelist );

        if ( count($where) ) {
            $arguments = array_diff_key( $arguments, $where );

            $arguments['where'] = isset($arguments['where']) ?
                array_merge( $arguments['where'], $where ) : $where;
        }

        return $arguments;
    }


    /**
     * Adding a touch of magic: in those cases where we have a straight-
     * forward auto_increment column in our Schema, use this function
     * to actually set it
     *
     * @return void
     * @param int     $increment_value Auto increment value produced by an insert
     */
    private function _set_auto_increment( $increment_value ) {
        foreach ( $this->schema() as $key => $value ) {
            if ( $value['extra'] == 'auto_increment' ) {
                $this->$key = $increment_value;
            }
        }
    }


    /**
     *
     *
     * @param unknown $name
     * @return unknown
     */
    private function _get_schema( $name ) {
        return sprintf("Nano_Db_%s", array_map( 'ucfirst', explode('_', $name ) ));
    }


    /**
     *
     *
     * @return unknown
     */
    private function _getMapper() {
        static $schema;

        if ( null == $schema ) {
            $schema = new Nano_Db_Schema_Mapper();
        }

        return $schema;
    }


}
