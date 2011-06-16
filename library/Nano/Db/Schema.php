<?php
abstract class Nano_Db_Schema{
    protected $_tableName   = null;
    protected $_schema      = null;
    protected $_primary_key = array();
    protected $_columns     = null;

    public function schema(){
        return $this->_schema;
    }

    public function key(){
        return $this->_primary_key;
    }

    public function columns(){
        if( null == $this->_columns ){
            $this->_columns = array_keys( $this->schema() );
        }

        return $this->_columns;
    }

    public function values(){
        $keys = $this->columns();

        return array();
    }

    public function table(){
        return $this->_tableName;
    }

    public final function __call( $method, $args ){
        static $schema;

        if( method_exists( 'Nano_Db_Schema_Mapper', $method ) ){
            //array_unshift( $args, $this );

            if( null == $schema ){
                $schema = new Nano_Db_Schema_Mapper();
            }

            return $schema->$method( $this, current($args) );

        }
    }

    protected function _get_schema( $name ){
        return sprintf("Nano_Db_%s", array_map( 'ucfirst', explode('_', $name ) ));
    }

    protected function _has_a( $relation ){
        //list( $key, $table, $foreign_key ) = array(
        //    $relation['key'], $relation['table'], $relation['foreign_key']
        //);
        //
        //$schema = $this->_get_schema( $table );
        //
        //$schema->getRow()->where( array(
        //    sprintf('`%s`.`%s`', $table, $foreign_key ) => $this->$key
        //));
        //
        //
    }

    protected function _has_many(){}


}
