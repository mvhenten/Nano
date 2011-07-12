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

    protected function _get_schema( $name ){
        return sprintf("Nano_Db_%s", array_map( 'ucfirst', explode('_', $name ) ));
    }

    protected function has_one( $schema, array $mapping ){
        $key         = reset( $mapping );
        $foreign_key = key( $mapping );


        $sth = $this->_getMapper()->search( new $schema(), array(
            'where' => array( $foreign_key => $this->$key ),
            'limit' => 1
        ));

        return $sth->fetch();
    }

    protected function has_many( $schema, array $mapping ){
        $key         = reset( $mapping );
        $foreign_key = key( $mapping );

        return $this->_getMapper()->search( new $schema(), array(
            'where' => array( $foreign_key => $this->$key )
        ));
    }

    protected function has_many_to_many( $schema, array $relation, $mapping ){
        $schema = new $schema();

        $relation_map = reset( $relation );
        $klass = key( $relation );
        $key   = current($mapping);

        $relation = new $klass();

        $query = sprintf('
            SELECT *
            FROM %s %s
            LEFT JOIN %s %s ON %s.%s = %s.%s
            WHERE %s.%s = ?
        ',
            $schema->table(), 'a',
            $relation->table(), 'b',
            'b', key( $relation_map ),
            'a', current( $relation_map ),
            'b', key( $mapping )
        );

        $this->_getMapper()->execute( $schema, $query, $this->$key );
    }

    private function _getMapper(){
        static $schema;

        if( null == $schema ){
            $schema = new Nano_Db_Schema_Mapper();
        }

        return $schema;
    }


}
