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
           && ( int == substr( $this->columnType( $this->key() ), 0, 3))){
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
            //@TODO type checking maybe?
            $this->_values[$name] = $value;
        }
    }

    public final function __call( $method, $args ){
        static $schema;
        if( method_exists( 'Nano_Db_Schema_Mapper', $method ) ){
            if( null == $schema ){
                $schema = new Nano_Db_Schema_Mapper();
            }
            return $schema->$method( $this, current($args) );
        }
    }

    protected function _get_schema( $name ){
        return sprintf("Nano_Db_%s", array_map( 'ucfirst', explode('_', $name ) ));
    }

    protected function has_one( $schema, array $mapping ){}

    protected function has_many( $schema, array $mapping ){}


}
