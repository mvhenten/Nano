<?php
/**
 * Usage:
 *
 * $query->filter('type', $type)->order('key')
 *
 *
 *
 */
//@todo implement fetch into model
//@todo implement delete
//@todo implement update
//@todo implement insert
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

    public function __construct( Nano_Db_Model $model ){
        $this->_model = $model;
    }

    /**
     * Returns a row count
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

        $this->query( $sql );

        if( $this->_sth ){
            $this->_sth->execute( $values );
            $count = $this->_sth->fetchColumn();
            $this->_sth = null;
        }

        return $count;
    }

    public function query( $query ){
        $dbh = Nano_Db::getAdapter( 'default' );
        $this->_sth = $dbh->prepare( $query );
    }

    /**
     * Magic getter: allows read-only to private class members
     *
     * @param string $name Name of the class member
     * @return mixed $value Value of class member or NULL
     */
    public function __get( $name ){
        if( ( $member = '_' . $name ) && property_exists( $this, $member ) ){
            return $this->$member;
        }
    }


    public function valid(){
        if( $this->_sth === null ){
            $this->execute();
        }

        if( $this->_sth !== false ){
            $result = $this->fetch();
            if( $result ){
                $this[] = $result;
                return true;
            }
            $this->_sth = false;
        }
        return isset($this[$this->_index]);

        return false;
    }

    public function current(){
        return $this[$this->_index];
    }

    public function next(){
        $this->_index++;
    }

    public function key(){
        return $this->_index;
    }

    public function rewind(){
        $this->_index = 0;
    }

    public function offsetGet( $index ){
        if( $this->offsetExists( $index ) ){
            return parent::offsetGet($index);
        }

        foreach( $this as $key => $value )
            if( $index == $key ) return $value;
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
            $this->_filter = array_merge( $this->_filter, $key );
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
        $this->query( $sql );

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

        if( count( $this->_filter ) > 0){
            $filter = array();
            foreach( $this->_filter as $rule ){
                list( $key, $value ) = $rule;
                preg_match( '/^(\w+)\s((\W+)|(LIKE?)|(NOT\sLIKE?))?/', $key, $match );

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


        $sql = join( "\n", $query );

        return array( $sql, $values, $query);
    }
}
