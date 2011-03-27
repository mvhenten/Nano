<?php
/**
 * class Nano_Db_Model
 */
class Nano_Db_Model extends ArrayObject{
    const FETCH_TABLENAME   = null;
    const FETCH_PRIMARY_KEY = null;
    const FETCH_LIMIT = 50;
    const FETCH_OFFSET = 0;

    protected $_properties = array(
        /* put model properties here */
    );

    protected $_settings = array(
        /* model settings can be stored here dynamically */
        'tableName'  => self::FETCH_TABLENAME,
        'primaryKey' => self::FETCH_PRIMARY_KEY
    );

    /**
     * Factory function. Override this function with:
     * return parent::get( __CLASS__, $key );
     *
     * Returns an instance of model $name, you *NEED* to override this
     * function if you want to call ::get() for your models.
     *
     * @param string $name Name of the model.
     * @param mixed $key Primary key or lookup properties for __construct
     * @return Nano_Db_Model A Model object
     */
    public static function get( $key, $name = __CLASS__ ){
        return new $name( $key );
    }

    /**
     * Class constructor. First argument must either be a valid value for
     * the primary key, or an array of properties to search for.
     *
     * @param mixed $settings Primary key or array of search parameters
     * @param mixed $properties Table properties or NULL
     */
    public function __construct( $properties = null, $settings = null ){
        $this->_settings = array_merge( $this->_settings, (array) $settings );

        if( is_scalar( $properties ) ){
            $properties = array(
                ($this->key()) => $properties
            );
        }
        
        if( is_array($properties) ){
            foreach( $properties as $key => $value ){
                $this->__set( $key, $value );
            }
        }
    }

    /**
     * Magic getter: returns model properties as class members.
     *
     * @param string $name Table column that may exist
     * @return mixed $value Value or NULL
     */
    public function __get( $name ){
        if( ( $method = 'get' . ucfirst($name) ) && method_exists($this, $method) ){
            return $this->$method();
        }
        else if( isset( $this->_properties[$name] ) ){
            return $this->_properties[$name];
        }
        else if( ($key = $this->_settings['primaryKey'] )
                && isset( $this->_properties[$key] ) ){//triggers a database lookup

            $query = new Nano_Db_Query( $this );
            $this->_properties = $query->current()->properties();
        }

        if( isset( $this->_properties[$name] ) ){
            return $this->_properties[$name];
        }
    }

    public function __set( $name, $value ){
        if( ( $method = 'set' . ucfirst($name) ) && method_exists($this, $method) ){
            $this->$method( $value );
        }
        else{
            $this->_properties[$name] = $value;
        }
    }

    public final function setProperty( $name, $value ){
        $this->_properties[$name] = $value;
    }

    public final function getProperty( $name ){
        if( key_exists( $name, $this->_properties ) ){
            return $this->_properties[$name];
        }
    }

    /**
     * Returns a Query object that fetches all entities of the kind
     * corresponding to the keys set in the model
     *
     * @param $instance
     */
    public function all( Nano_Db_Model $instance = null ){
        if( null == $instance ){
            $instance = $this;
        }

        return Nano_Db_Query::get( $instance );
    }


    /**
     * Update or insert $instance or $this
     *
     * @param Nano_Db_Model $instance
     * @return integer $key
     */
    public function put( Nano_Db_Model $instance = null ){
        if( $instance === null ){
            $instance = $this;
        }

        $qh = new Nano_Db_Query( $instance );

        $last_insert = $qh->put();

        if( $last_insert > 0 ){
            $this->{$this->key()} = $last_insert;
        }

        return $this;
    }

    /**
     * Deletes this $instance or $this instance
     *
     * @param Nano_Db_Model $instance Instance to delete
     * @return bool $success Success
     */
    public function delete( $key = null, $value = null ){
        $qr = new Nano_Db_Query( $this );
        return $qr->delete( $key, $value );
    }

    public function properties(){
        return $this->_properties;
    }

    //@todo refactor; key should return key;
    //@todo refactor; key may be an array;
    // @todo refactor query class to reflect this
    public function key($value=null){
        return $this->keyName();
    }

    public function keyName(){
        return $this->_settings['primaryKey'];
    }

    public function getPrimaryKey(){
        $keyname = $this->_settings['primaryKey'];
        $properties = $this->properties();

        if( isset( $properties[$keyname] ) ){
            return $properties[$keyname];
        }
    }

    public function tableName(){
        return $this->_settings['tableName'];
    }
}
