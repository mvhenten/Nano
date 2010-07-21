<?php
/**
 * $model = new Nano_Db_Model( array(
 *      tableName = 'item',
 *      primaryKey = 'id'
 * ));
 *
 */

class Nano_Db_Model extends ArrayObject{
    const FETCH_TABLENAME   = null;
    const FETCH_PRIMARY_KEY = 'id';
    const FETCH_LIMIT = 25;
    const FETCH_OFFSET = 0;

    protected $_properties = array(
    );

    protected $_settings = array(
        'tableName'  => self::FETCH_TABLENAME,
        'primaryKey' => self::FETCH_PRIMARY_KEY
    );

    public function __construct( $settings = array(), $properties = array() ){
        $this->_settings = array_merge( $this->_settings, $settings );
        $this->_properties = array_merge( $this->_properties, $properties );
    }

    public function __get( $name ){
        if( in_array( $name, $this->_properties ) ){
            return $this->_properties[$name];
        }
    }

    public function __set( $name, $value ){
        $this->_properties[$name] = $value;
    }

    /**
     * Returns the first objeckt matching $key.
     * Object will be an instance of $this based on class, primary key and table name
     *
     * @param mixed $key Value for the primary key
     * @return Nano_Db_Model A Model object
     */
    public static function get( $key ){
        $this->key( $key );

        $qh = new Nano_Db_Query( $this );

        foreach( $qh as $qr )
            return $qr;
    }

    /**
     * Returns a Query object that fetches all entities of the kind corresponding
     * to $this model
     *
     * @param $instance
     */
    public function all( $instance = null ){
        if( null == $instance ){
            $instance = $this;
        }

        return new Nano_Db_Query( $instance );
    }

    /**
     * Update or insert $instance or $this
     *
     * @param Nano_Db_Model $instance
     * @return integer $key
     */
    public function put( $instance = null ){
        if( $instance == null ){
            $instance == $this;
        }

        $qh = new Nano_Db_Query( $instance );

        return $qh->put();
    }

    /**
     * Deletes this $instance or $this instance
     *
     * @param Nano_Db_Model $instance Instance to delete
     * @return bool $success Success
     */
    public function delete( $instance = null ){
        if( null == $instance ){
            $instance = $this;
        }

        $qh = new Nano_Db_Query( $instance );

        return $qh->put();
    }

    public function properties(){
        return (object) $this->_properties();
    }

    public function primaryKey(){
        //@todo include checks for key existance
        return $this->_settings['primaryKey'];
    }

    public function tableName(){
        if( null == $this->_settings['tableName'] ){
            $name = get_class( $this );

            if( $name !== 'Nano_Db_Model' ){
                $this->_settings['tableName'] = strtolower( $name );
            }
            else{
                throw new Exception( 'Table name is not set' );
            }
        }

        return $this->_settings['tableName'];
    }


}
