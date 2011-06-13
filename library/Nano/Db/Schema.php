<?php
abstract class Nano_Db_Schema{
    protected $_tableName   = null;
    protected $_schema      = null;
    protected $_primary_key = array();

    public function schema(){
        return $this->_schema;
    }

    public function primaryKey(){
        return $this->_primary_key;
    }

    public function columns(){
        return array_keys( $this->schema() );
    }

    public function table(){
        return $this->_tableName();
    }

    protected function __call( $method, $args ){
        if( ($method = '_get_' . $method ) && method_exists( $method, $this ) ){
            return $this->$method();
        }
    }

    protected function _get_schema( $name ){
        return sprintf("Nano_Db_%s", array_map( 'ucfirst', explode('_', $name ) ));
    }

    protected function _has_a( $relation ){
        list( $key, $table, $foreign_key ) = array(
            $relation['key'], $relation['table'], $relation['foreign_key']
        );

        $schema = $this->_get_schema( $table );

        $schema->getRow()->where( array(
            sprintf('`%s`.`%s`', $table, $foreign_key ) => $this->$key
        ));


    }

    protected function _has_many(){}


}
