<?php
/**
 * class Nano_Db_Query
 * A minimalistic database abstraction based on PDO
 *
 * Nano_Db_Query implements a minimal database abstraction layer, loosely
 * inspried by Google's Datastore API and Django's models.
 * It is intended to work in tango with Nano_Db_Model.
 * 
 * This implementation aims to ease 80% of most used database tasks, and not
 * a full query abstraction. It therefore allows for direct access to PDO
 * trough 'query' if desired.
 *
 * This class implements the ArrayIterator and serves as syntactic sugar for
 * the PDO databse abstraction by allowing chaining and a simple query building
 * function.
 *
 * Example:
 * <code>
 * <?php
 *   // Model_Example is a class extending Nano_Db_Model
 *   $item = Model_Example::get(1); //fetch by id
 *   // fetch using filters
 *   $items = $item->all()->filter('type', 1)->filter(array('name LIKE'=>'%foo%', 'score <' => 5))
 *   // optional ordering and limit
 *   $items = $item->all()->filter('type !=', 1)->limit(100)->order('priority DESC');
 *   // update item
 *   $item->name = "foobaz";
 *   $item->put()
 *   // create item
 *   $new = new Model_Example( array('name'=>'foobaz') ); // set properties in constructor
 *   $new->put();
 * ?>
 * </code>
 *
 * @package Nano_Db
 *
 * @todo merge prepare/execute into one function (query?) that accepts sql and values
 * @todo rename execute to less ambiguous naming
 * @todo filter shoud not use key => value pairs, to allow for multipel ids ( e.g. shuld follow array($key, $value))
 * @todo put and delete must allow for filter-like syntax.
 */
class Nano_Db_Query extends ArrayIterator{
    const FETCH_LIMIT = 25;
    const FETCH_OFFSET = 0;
    const FETCH_ORDER  = null;
    const FETCH_FILTER = null;

    private $_limit     = self::FETCH_LIMIT;
    private $_offset    = self::FETCH_OFFSET;
    private $_where    = array();
    private $_orwhere   = array();
    private $_order     = array();
    private $_group     = array();
    private $_index     = 0;
    private $_model     = null;

    private $_sth;

    /**
     * Class constructor
     *
     * @param Nano_Db_Model $model A Nano_Db_Model instance
     */
    public function __construct( Nano_Db_Model $model ){
        $this->_model = $model;
    }

    /**
     * Iterator function: check if current offset is valid
     *
     * This function initiates a query object if none has been defined
     * and returns wether we are allowd to iterate over the query object
     *
     * @return bool $is_valid If the current resultset can be iterated
     */
    public function valid(){
        if( $this->_sth === null ){
            $this->all();
        }

        if( $this->_sth !== false ){
            $result = $this->fetch();

            $model = $this->_model;

            if( $result ){
                $this[] = new $model( $result );
                return true;
            }
            $this->_sth = false;
        }
        return isset($this[$this->_index]);

        return false;
    }

    /**
     * Iterator function: returns the value at the current index
     *
     * @return mixed $value Query result value
     */
    public function current(){
        return $this[$this->_index];
    }

    /**
     * Iterator function: increase the current index
     *
     * @return void
     */
    public function next(){
        $this->_index++;
    }

    /**
     * Iterator function: returns the current index
     *
     * @return integer $index
    */
    public function key(){
        return $this->_index;
    }

    /**
     * Iterator function: rewind the current index
     *
     * @return void
     */
    public function rewind(){
        $this->_index = 0;
    }

    /**
     * Iterator function: returns $value for array notation[index] or array
     * access ( foreach etc. ), The parent offsetGet will call rewind for us.
     *
     * @return mixed $value
     */
    public function offsetGet( $index ){
        if( $this->offsetExists( $index ) ){
            return parent::offsetGet($index);
        }

        foreach( $this as $key => $value )
            if( $index == $key ) return $value;
    }

    /**
     * Returns current query's row count
     *
     * @return integer $count Row count
     */
    public function count(){
        list( $sql, $values, $query ) = $this->build();
        $count = 0;

        array_pop( $query );//remove limit, offset

        $sql = join( "\n", $query );

        if( stripos( $sql, 'group by' ) > -1 ){
            $sql = sprintf('SELECT count(*) AS `total` FROM (SELECT * %s) `tmp_name`', $sql);
        }
        else{
            $sql = 'SELECT count(*) AS `total` ' . $sql;
        }

        $this->prepare( $sql );

        if( $this->_sth ){
            $this->_sth->execute( $values );
            $count = $this->_sth->fetchColumn();
            $this->_sth = null;
        }

        return $count;
    }

    public function put( $model = null ){
        if( null == $model ){
            $model = $this->_model;
        }


        $query = array();

        $keyname  = $model->key();
        $table    = $model->tableName();
        $props    = $model->properties();
        $key      = $model->$keyname;

        unset( $props[$keyname] );

        if( $key ){
            $set = array();
            $query[] = sprintf('UPDATE `%s`', $model->tableName() );

            foreach( $props as $key => $value ){
                $set[] = sprintf('`%s` = ?', $key );
            }

            $query[] = 'SET' . join( ",\n", $set );
            $query[] = sprintf('WHERE `%s` = ?', $keyname );
            $props[] = $model->$keyname;
        }
        else{
            $keys = array_keys( $props );
            foreach( $keys as $i => $key ) $keys[$i] = '`' . $key . '`';
            $values = array_fill( 0, count($keys), '?');

            $query[] = sprintf('INSERT INTO `%s`', $model->tableName());
            $query[] = '(' . join( ",", $keys ) . ')';
            $query[] = 'VALUES ( ' . join( ",", $values ) . ')';
        }

        $this->query( join("\n", $query ), array_values($props) );
        $model->id = $this->lastInsertId();
    }

    public function delete( $key = null, $value = null ){
        if( is_array($key) ){
            foreach( $key as $rule ){
                $this->orWhere( $rule );                         
            }
        }
        else if( $key && $value){
            $this->orWhere( $key, $value );
        }
        
        list( $sql, $values, $query ) = $this->build();
        
        
        list( $from, $where ) = $query;

        $sql = sprintf('DELETE %s %s', $from, $where);

        $this->query( $sql, $values );
    }

    /**
     * Prepare and execute query.
     *
     * This function uses 'prepare' and 'execute' for save parameter quoting
     *
     * @param string $sql SQL query to run
     * @param array $values Values for variable substitution
     * @return void
     */
    public function query( $sql, $values ){       
        $sql = str_replace( '?', '%d', $sql );
        $sql = vsprintf( $sql, $values );
        
        
        var_dump( $sql );
        exit();


        $where = '';



        $this->prepare( $sql );
        if( $this->_sth ){
            $this->_sth->execute( $values );

            if( $this->_sth->errorCode() !== '00000' ){
                $info = $this->_sth->errorInfo();
                throw new Exception( join( "\n", $info ) );
            }
        }
    }

    /**
     * Prepare wrapper
     */
    private function prepare( $query ){
        $dbh = $this->getAdapter();
        $this->_sth = $dbh->prepare( $query );
    }

    private function lastInsertId(){
        return $this->getAdapter()->lastInsertId();
    }

    private function getAdapter( $name = 'default' ){
        return Nano_Db::getAdapter( $name );
    }

    /**
     * Set fetch limit for the current query
     *
     * @param int $limit Fetch limit
     * @return Nano_Db_Query $this Fluent interface
     */
    public function limit( $int ){
        $this->_limit = $int;
        return $this;
    }

    /**
     * Set fetch offset for the current query
     *
     * @param int $offset Fetch offset
     * @return Nano_Db_Query $this Fluent interface
     */
    public function offset( $int ){
        $this->_offset = $int;
        return $this;
    }

    /**
     * Set fetch order
     *
     * @param mixed $order Order may be single statement, or an array of orders
     * @return Nano_Db_Query $this Fluent interface
     */
    public function order( $order ){
        foreach( (array) $order as $value )
            $this->_order[] = $value;

        $this->_order = array_unique( $this->_order );

        return $this;
    }

    public function group( $values ){
        foreach( (array) $values as $value )
            $this->_group[] = $value;

        return $this;
    }

    private function fetch( $mode = PDO::FETCH_ASSOC ){
        if( $this->_sth ){
            return $this->_sth->fetch( $mode );
        }
    }

    private function all(){
        list( $sql, $values, $query ) = $this->build();
        
        $sql = 'SELECT * ' . $sql;

        $this->query( $sql, $values );
    }

    /**
     * Set a filter (WHERE / AND) for the current query
     *
     * @param mixed $key Takes a key/value paire, or optionally an array of key/values
     * @param mixed $value If key is a string, value is expected as second argument
     *
     * @return Nano_Db_Query $this Fluent interface
     */
    public function where( $key, $value = null ){
        if( is_array( $key ) ){
            foreach( $key as $filter ){
                $this->where( $filter[0], $filter[1] );
            }
        }
        else{
            $this->_where[] = array( $key, $value );
        }

        return $this;
    }

    /**
     * Set a filter for WHERE a OR b queries
     *
     * Parens are added around the entire "OR" section, so an extra AND may
     * be used as extra filter
     *
     * @param mixed $key The key or an array of key/value pairs
     * @param mixed $value Value for key if key is a string, or null
     * @return Nano_Db_Query $this A fluent interface
     */
 
    public function orWhere( $key, $value = null ){
        if( is_array( $key ) ){
            $this->_orwhere[] = $key;            
        }
        else if( is_string($key) && null !== $value ){
            $this->_orwhere[] = array( array( $key, $value ) );            
        }
        else{
            throw new Exception( 'invalid syntax: either a key,value pair of a list of pairs expected');
        }
        return $this;
    }
    
    /**
     * Construct and validate filter pairs
     *
     * Filters are given as array( key, value ) or an array of such key/value pairs
     * If an array of key/value pairs is supplied, these are treated as AND queries
     * and joined. $values is filled with all successfully parsed key/value filter pairs
     *
     * This function checks for operators !=,=,LIKE,NOT LIKE
     *
     * @param array $rule A key/value array or array of key value arrays
     */
    private function buildFilter( $rule, &$values = array() ){
        if( is_array( reset($rule) ) ){
            $rules = $rule;
            $filter = array();

            foreach( $rules as $rule ){
                $sub = $this->buildFilter( $rule, $values );
                if( null !== $sub ){
                    $filter[] = $sub;
                }
            }
            
            return join( " AND ", $filter );
        }
        else{
            list( $key, $value ) = $rule;

            if( strpos( $key, ' ') == false ){//key is a simple string, add an operator
                $match = array(null, $key, '=' );
            }
            else{// if matching, key contains LIKE, NOT LIKE or a != or = operator
                preg_match( '/^(\w+)\s((!=)|([<>=])|(LIKE?)|(NOT\sLIKE?))?/', $key, $match );
            }

            if( count($match) > 2 ){
                list( $full, $name, $op ) = $match;
                $values[] = $value;

                return sprintf( "`%s` %s ?", $name, $op );
            }
        }
    }

    /**
     * Compose the actual SQL query
     */
    private function build(){
        $adapter = Nano_Db::getAdapter( 'default' );

        $query  = array();
        $values = array();

        $query[] = sprintf( 'FROM `%s`', $this->_model->tableName());

        $model = $this->_model;

        if( ($key = $model->{$model->key()} ) && null !== $key ){
            $query[] = sprintf( 'WHERE `%s` = ?', $model->key() );
            $values[] = $key;
            $sql = join( "\n", $query );
            return array( $sql, $values, $query);
        }
        
        $filter = array();
        
        if( count( $this->_orwhere ) > 0 ){
            $or = array();
            foreach( $this->_orwhere as $rules ){
                $or[] = sprintf('( %s )', $this->buildFilter( $rules, $values ));
            }
            
            $filter[] = sprintf( '( %s )', join( ' OR ', $or ));
        }

        if( count( $this->_where ) > 0 ){
            $filter[] = $this->buildFilter( $this->_where, $values );
        }
        
        if( ! empty( $filter ) ){
            $query[] = 'WHERE ' . join( ' AND ', $filter );            
        }
        
        if( count( $this->_group ) ){
            $group = array();
            foreach( $this->_group as $value ){
                $group[] = sprintf('`%s`', $value );
            }

            $query[] = 'GROUP BY ' . join( ",", $group );
        }

        if( count( $this->_order) > 0 ){
            $order = array();
            foreach( $this->_order as $value ){
                preg_match( '/(^[-+])(\w+)|(^\w+)?/', $value, $match );

                list( $full, $mod, $value ) = $match;

                if( strlen($mod) == 0 ){
                    $value = $match[3];
                }

                $order[] = sprintf('%s`%s`', $mod, $value );
            }

            $query[] = sprintf( 'ORDER BY %s', join(",", $order ));
        }

        $query[] = sprintf( 'LIMIT %d, %d', $this->_offset, $this->_limit );

        $sql = join( "\n", $query );

        return array( $sql, $values, $query);
    }
}