<?php
class Nano_Db_Mapper{
    /**
     * Placeholder for db adapter
     * Override this if it's not 'default'
     * @var string $adapter default
     */
    protected $_adapter = 'default';

    /**
     * Placeholder for db table.
     * Override this variable when extending this class
     * @var string $tableName
     */
    protected $_tableName;

    /**
     * Primary key for this database table, id by default
     * @var string $key Name of the primary key
     */
    protected $_primaryKey = 'id';

    /**
     * Class constructor
     * @param string $tableName (optional) Table for this mapper
     * @param string $primary Primary key name ( defaults: id );
     */
    public function __construct( $tableName = null, $primary = 'id' ){
        $this->_primaryKey  = $primary;
        if( null == $this->_tableName && null !== $tableName ){
            $this->_tableName = $tableName;
        }
    }

    /**
     * Fetch object properties into the model
     *
     * @param Some_Model $model Model to fetch into
     * @return void
     */
    public function find( $model ){
        $key = $this->_primaryKey;
        $obj = $this->fetchById( $model->$key );

        foreach( $obj as $key => $value ){
            $model->$key = $value;
        }
    }

    /**
     * update or insert this model into the database
     * @param Some_Model $model
     */
    public function save( $model ){
        $values = $model->toArray();
        $key = $this->_primaryKey;

        $keys = array_map( array( $this, '_dasherize' ), array_keys($values) );
        $values = array_combine( $keys, $values );

        if( null == $model->$key ){
            $id = $this->getDb()->insert( $this->_tableName, $values );
            $model->$key = $id;
        }
        else{
            $this->getDb()->update( $this->_tableName, $values, $key );
        }
    }

    /**
     * Delete this object from the database using it's primary key
     * @param Some_Model $model Model to delete
     * @return void
     */
    public function delete( $model ){
        $key = $this->_primaryKey;

        $this->getDb()->query( sprintf(
            'DELETE FROM `%s` WHERE %s = ?',
            $this->_tableName,
            $this->_primaryKey
        ), array( $model->$key ) );
    }

    /**
     * A simple find by id
     * Performs a simple select * for current table
     *
     * @param int $id Primary key value
     * @return object $tableRow
     */
    private function fetchById( $id ){
        $obj = $this->getDb()->fetchRow($this->_tableName, array($this->_primaryKey=>$id) );
        return $obj;
    }

    private function primaryKey(){
        return $this->_primaryKey;
    }

    /**
     * Fetch default database adapter
     * @return Nano_Db_Adapter $adapter
     */
    private function getDb(){
        return Nano_Db::getAdapter( $this->_adapter );
    }

    private function _dasherize( $key ){
        preg_match_all('/[A-Z][^A-Z]*/',ucfirst($key),$results);
        $results = array_map( 'strtolower', $results[0] );
        return join( '_', $results );
    }
}
