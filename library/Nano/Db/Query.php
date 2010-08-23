<?php
/**
 * class Nano_Db_Query
 * A minimalistic database abstraction
 *
 * Nano_Db_Query implements a minimal database abstraction layer, loosely based
 * on Google's Datastore API and Django's models. It is intended to work in
 * tango with Nano_Db_Model.
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
 */
//@todo implement delete
//@todo implement update
class Nano_Db_Query extends ArrayIterator{
    const FETCH_LIMIT = 25;
    const FETCH_OFFSET = 0;
    const FETCH_ORDER  = null;
    const FETCH_FILTER = null;

    private $_limit     = self::FETCH_LIMIT;
    private $_offset    = self::FETCH_OFFSET;
    private $_filter    = array();
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
            $this->execute();
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

        $table  = $model->tableName();
        $props  =  $model->properties();
        $key    = $model->$keyname;

        if( $key ){

        }
        else{
            $keys = array_keys( $props );
            foreach( $keys as $i => $key ) $keys[$i] = '`' . $key . '`';
            $values = array_fill( 0, count($keys), '?');

            $query[] = sprintf('INSERT INTO `%s`', $model->tableName());
            $query[] = '(' . join( ",", $keys ) . ')';
            $query[] = 'VALUES ( ' . join( ",", $values ) . ')';
        }


        //$dbh = $this->getAdapter();
        //$sth = $dbh->prepare( join("\n", $query) );

        $this->prepare( join("\n", $query ) );

        if( $this->_sth ){
            $this->_sth->execute( array_values( $props ) );
            $id = $this->lastInsertId();
            $model->id = $id;
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
     * Set a filter (WHERE / AND) for the current query
     *
     * @param mixed $key Takes a key/value paire, or optionally an array of key/values
     * @param mixed $value If key is a string, value is expected as second argument
     *
     * @return Nano_Db_Query $this Fluent interface
     */
    public function filter( $key, $value = null ){
        if( is_array( $key ) ){
            foreach( $key as $op => $value ){
                $this->_filter[] = array( $op, $value );
            }
        }
        else{
            $this->_filter[] = array( $key, $value );
        }

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

    private function execute(){
        list( $sql, $values, $query ) = $this->build();

        $sql = 'SELECT * ' . $sql;
        $this->prepare( $sql );

        if( $this->_sth ){
            $this->_sth->execute( $values );
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
        }
        else{
            if( count( $this->_filter ) > 0){
                $filter = array();
                foreach( $this->_filter as $rule ){
                    list( $key, $value ) = $rule;

                    if( ctype_alpha( $key ) ){//key is a simple string, add an operator
                        $match = array(null, $key, '=' );
                    }
                    else{// if matching, key contains LIKE, NOT LIKE or a != or = operator
                        preg_match( '/^(\w+)\s((\W+)|(LIKE?)|(NOT\sLIKE?))?/', $key, $match );
                    }

                    if( count($match) > 2 ){
                        list( $full, $name, $op ) = $match;
                        $filter[] = sprintf( "`%s` %s ?", $name, $op );
                        $values[] = $value;
                    }
                }

                $query[] = sprintf( 'WHERE %s', join( ' AND ', $filter ));
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
        }

        $sql = join( "\n", $query );

        return array( $sql, $values, $query);
    }
}
