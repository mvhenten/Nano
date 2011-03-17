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
 *   $items = $item->all()->where('type', 1)->filter(array('name LIKE'=>'%foo%', 'score <' => 5))
 *   // optional ordering and limit
 *   $items = $item->all()->where('type !=', 1)->limit(100)->order('priority DESC');
 *   // or
 *   $items = $item->all()->where( 'type LIKE', 'amd%')->orWhere('name LIKE', '%x%')
 *   // or complex
 *   $items = $item->all()->where( array('foo',1), array('biz',2) )->orWhere('expired',1);
 *   // update item
 *   $item->name = "foobaz";
 *   $item->put()
 *   // create item
 *   $new = new Model_Example( array('name'=>'foobaz') ); // set properties in constructor
 *   $new->put();
 *   // delete can take complex arguments that will be ran trough orWhere
 *   $new = Model_Example::get()->delete(
 *      array('id IN', array(1,2,3)),
 *      array('name LIKE', '%a%')
 *    );
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
    const FETCH_LIMIT = 50;
    const FETCH_OFFSET = 0;
    const FETCH_ORDER  = null;
    const FETCH_FILTER = null;

    private $_limit     = self::FETCH_LIMIT;
    private $_offset    = self::FETCH_OFFSET;
    private $_where    = array();
    private $_orwhere   = array();
    private $_order     = array();
    private $_joinLeft  = array();
    private $_group     = array();
    private $_index     = 0;
    private $_model     = null;

    private $_sth;

    /**
     * Class constructor
     *
     * @todo most functions depend on $model for TableName
     *
     *
     * @param Nano_Db_Model $model A Nano_Db_Model instance
     */
    public function __construct( Nano_Db_Model $model ){
        $this->_tableName = $model->tableName();

        $this->setModel( $model );
    }

    /**
     * Factory method: Creates a query for $model. This function
     * will also instantiate $model for you to allow quick chaining.
     *
     * @param mixed $model Model name or $model instance
     * @param array $construct Constructor variables for $model
     */
    static public function get( $model, $construct = null ){
        if( is_scalar( $model )){
            if( class_exists( $model ) ){
                $instance = new $model( $construct );
            }
            else if( class_exists( "Model_" . $model ) ){
                $model = "Model_" . $model;
                $instance = new $model( $construct );
            }
            else{
                throw new Exception( "$model cannot be resolved" );
            }
        }
        else{
            $instance = $model;
        }

        $qr = new Nano_Db_Query( $instance );

        //@todo this is automagic. is this a good idea?
        // adding properties of instance as filter
        foreach( $instance->properties() as $key => $value ){
            $qr->where( sprintf("%s =", $key), $value);
        }

        return $qr;
    }

    public function setModel( Nano_Db_Model $model ){
        $this->_model = $model;
        return $this;
    }

    public function getModel(){
        return $this->_model;
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

            $model = $this->getModel();

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
     * 'Pluck' a property: foreach result in this query, return the requested property(ies)
     *
     * @param string $property A property from the model requested
     */
    public function pluck( $property ){
        $collect = array();

        foreach( $this as $obj ){
            $collect[] = $obj->$property;
        }

        return $collect;
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

        // NOTE that __NONE__ is a workaround for tables without
        // primary key. Primary key is used falsely here;
        if(  $keyname !== '__NONE__' && $key ){
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

        return $this->lastInsertId();
    }

    public function delete( $key = null, $value = null ){
        if( is_array($key) ){
            foreach( $key as $rule ){
                list($key, $value) = $rule;
                $this->orWhere( $key, $value );
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
        //print_r(str_replace("?", '"%s"', $sql));
        //print_r(vsprintf(str_replace("?", '"%s"', $sql), $values ));
        //print_r(sprintf(, $values));
        $values = (array) $values;
        $this->prepare( $sql );

        if( $this->_sth ){
            $this->_sth->execute( $values );

            if( $this->_sth->errorCode() !== '00000' ){
                $info = $this->_sth->errorInfo();
                throw new Exception( join( "\n", $info ) . "\n---\n" . $sql );
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

    public function getLimit(){
        return $this->_limit;
    }

    public function getOffset(){
        return $this->_offset;
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
    public function where( $key, $value = null, $table = null ){
        if( null == $table ){
            $table = $this->_tableName;
        }
        if( is_array( $key ) ){
            foreach( $key as $filter ){
                $this->where( $filter[0], $filter[1], $table );
            }
        }
        else{
            $this->_where[] = array( $key, $value, $table );
        }

        return $this;
    }

    /**
     * Set a filter for WHERE a OR b queries
     *
     * Parens are added around the entire "OR" section, so an extra AND may
     * be used as extra filter
     * @todo add table parameter
     *
     * @param mixed $key The key or an array of key/value pairs
     * @param mixed $value Value for key if key is a string, or null
     * @return Nano_Db_Query $this A fluent interface
     */
    public function orWhere( $key, $value = null, $table = null ){
        if( null == $table ){
            $table = $this->_tableName;
        }
        if( is_array( $key ) ){
            // adding an array results in AND query with parenteses for OR
            foreach( $key as $index => $filter ){
                if( count($filter) < 3 ){
                    array_push( $filter, $table );//add $table
                }
                $key[$index] = $filter;
            }
            $this->_orwhere[] = $key;
        }
        else if( is_string($key) && null !== $value ){
            $this->_orwhere[] = array( array( $key, $value, $table ) );
        }
        else{
            throw new Exception( 'invalid syntax: either a key,value pair of a list of pairs expected');
        }
        return $this;
    }

    /**
     * Do a trivial left-join
     *
     * Basic left join to filter on items from a different table
     *
     * @param $table Name of the table to join
     * @param $key Key from originating table
     * @param $value Key from join table
     */
    public function leftJoin( $joinTable, $column, $value = null ){
        if( ! is_array( $column ) ){
            $column = array($column=>$value);
        }

        $table = $this->_tableName;
        $joins = array();
        foreach( $column as $key => $value ){
            if( strpos( $key, ' ') == false ){//key is a simple string, add an operator
                $match = array(null, $key, '=' );
            }
            else{// if matching, key contains LIKE, NOT LIKE or a != or = operator
                preg_match( '/^(\w+)\s((!=)|([<>=])|(like?)|(not\slike?))?/', strtolower($key), $match );
            }

            if( count($match) > 2 ){
                list( $full, $key, $op ) = $match;
                $joins[] = sprintf("`%s`.`%s` %s `%s`.`%s`",
                    $joinTable, $key, strtoupper($op), $table, $value );
            }
        }

        $this->_joinLeft[] = sprintf( "LEFT JOIN `%s` ON %s",
            $joinTable, join( 'AND', $joins ) );

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
            list( $key, $value, $table ) = $rule;

            if( strpos( $key, ' ') == false ){//key is a simple string, add an operator
                $match = array(null, $key, '=' );
            }
            else{// if matching, key contains LIKE, NOT LIKE or a != or = operator
                preg_match( '/^(\w+)\s((!=)|([<>=])|(like?)|(not\slike?)|(in)|(not\sin))?/', strtolower($key), $match );
            }

            if( count($match) > 2 ){
                list( $full, $name, $op ) = $match;

                if( ($op == 'in' || $op == 'not in') && is_array($value) ){
                    $values = array_merge( $values, $value );
                    $replace = sprintf( '(%s)',join(',', array_fill(0, count($value), '?')));
                }
                else{
                    $values[] = $value;
                    $replace = '?';
                }

                return sprintf( "`%s`.`%s` %s %s", $table, $name, strtoupper($op), $replace );
            }
        }
    }

    /**
     * Compose the actual SQL query
     */
    public final function build(){
        $adapter = Nano_Db::getAdapter( 'default' );

        $query  = array();
        $values = array();

        $query[] = sprintf( 'FROM `%s`', $this->_tableName);

        if( ($key = $this->_model->{$this->_model->key()} ) && null !== $key ){
            $query[] = sprintf( 'WHERE `%s` = ?', $this->_model->key() );
            $values[] = $key;
            $sql = join( "\n", $query );
            return array( $sql, $values, $query);
        }

        $filter = array();

        if( count( $this->_joinLeft ) > 0 ){
            foreach( $this->_joinLeft as $join ){
                $query[] = $join;
            }
        }

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
