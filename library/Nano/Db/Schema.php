<?php
abstract class Nano_Db_Schema{
    protected $_tableName   = null;
    protected $_schema      = null;
    protected $_primary_key = array();
    protected $_columns     = null;

    private $_values      = array();

    public function schema(){
        return $this->_schema;
    }

    public function key(){
        if( count( $this->_primary_key ) == 1 ){
            return $this->_primary_key[0];
        }
        else{
            throw new Exception('Not implemented...' );
        }
        return $this->_primary_key;
    }

    public function columns(){
        if( null == $this->_columns ){
            $this->_columns = array_keys( $this->schema() );
        }

        return $this->_columns;
    }

    public function columnType( $column ){
        if( in_array( $column, $this->columns() ) ){
            return $this->_schema[$column]['type'];
        }
    }

    public function values(){
        return $this->_values;
    }

    public function setValues( array $values ){
        foreach( $values as $key => $value ){
            $this->__set( $key, $value );
        }
    }

    public function table(){
        return $this->_tableName;
    }

    public final function __construct( $id = null ){
        if( is_numeric( $id )
           && ( 'int' == substr( $this->columnType( $this->key() ), 0, 3))){
            $this->load( $id );
        }
        else if( is_array( $id ) ){
            $this->setValues( $id );
        }
    }

    public final function __get( $name ){
        if( in_array( $name, $this->columns() ) ){
            if( isset( $this->_values[$name] ) ){
                return $this->_values[$name];
            }
        }
    }

    public final function __set( $name, $value ){
        if( in_array( $name, $this->columns() ) ){
            $this->_values[$name] = $value;
        }
    }

    public final function __call( $method, $args ){
        if( method_exists( 'Nano_Db_Schema_Mapper', $method ) ){
            return $this->_getMapper()->$method( $this, current($args) );
        }
    }

    /**
     * Defines a 'has_one' unique relation established trough $schema
     *
     * @param string $schema Name of the schema class that we have one
     * @param array $mapping $foreign_key => $key relation (as in $schema->$foreign_key)
     */
    protected function has_one( $schema, array $mapping ){
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
     * @param string $schema Name of the schema class that we have many of
     * @param array $mapping $foreign_key => $key relation (as in $schema->$foreign_key)
     */
    protected function has_many( $schema, array $mapping ){
        $key         = reset( $mapping );
        $foreign_key = key( $mapping );

        return $this->_getMapper()->search( new $schema(), array(
            'where' => array( $foreign_key => $this->$key )
        ));
    }

    /**
     * Defines a 'many_to_many' relation established trough $schema
     *
     * @param string $relation Name of the 'belongs_to' function in $schema
     * @param string $schema Name of the schema class that has the belongs_to
     * @param array $mapping $foreign_key => $key relation (as in $schema->$foreign_key)
     */
    protected function has_many_to_many( $relation, $schema, $mapping ){
        $schema = new $schema();

        $key         = reset( $mapping );
        $foreign_key = key( $mapping );

        list($relation, $mapping ) = $schema->$relation();

        return $this->_getMapper()->many_to_many( new $relation(), array(
            'join'  => array( $schema->table() => $mapping ),
            'where' => array( $foreign_key => $this->$key )
        ));
    }

    /**
     * Defines a 'belongs_to' relation for use in many_to_may relations
     *
     * @param string $schema Name of the schema class this table belongs to
     * @param array $mapping $foreign_key => $key relation (as in $schema->$foreign_key)
     */
    protected function belongs_to( $schema, array $mapping ){
        return array( new $schema(), $mapping );
    }

    protected function _get_schema( $name ){
        return sprintf("Nano_Db_%s", array_map( 'ucfirst', explode('_', $name ) ));
    }

    private function _getMapper(){
        static $schema;

        if( null == $schema ){
            $schema = new Nano_Db_Schema_Mapper();
        }

        return $schema;
    }


}
