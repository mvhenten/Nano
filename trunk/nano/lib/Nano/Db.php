<?php
/**
 * Nano Database wrapper: This class extends PDO to provide some utility
 * functions such as 'insert', 'update', 'select'
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @copyright Copyright (c) 2009 Matthijs van Henten
 */
class Nano_Db extends PDO{
    private $sth;

    /**
     * Constructor overrides PDO::_construct
     * All configuration options may now be passed as an key => value array
     *
     * @param $config array('dbUser'=>'','dbPassword'=>, 'dbHost'=> ... ) etc.
     * @return Nano_Db $db
     */
    public function __construct( $config ){
        $config = $this->parseConfig( $config );

        try{
            parent::__construct(
                $config->dsn,
                $config->dbUser,
                $config->dbPassword
            );
        }
        catch( Exception $e ){
			die( sprintf( 'Cannot open database: %s', $e->getMessage() ) );
        }
    }

    /**
     * Performs a simple select query ( SELECT * FROM $tableName WHERE $options )
     *
     * @param string $table Table to query from
     * @param string $where Where clause. use PDO array(':key' => 'value' ) replacements or ?
     * @param mixed $values PDO array or a single value to be replaced
     * @param array $columns Optional array of columns to fetch

     *  @return array $array with Objects.
     */
    public function select( $table, $where = null, $values = null, $columns = null ){
        $values = (array) $values;

        if( null !== $columns ){
            $columns = array_map( 'sprintf', array_fill( 0, count($columns), '`%s`'), $columns );
            $select = array( 'SELECT ' . "\n" . join( ",\n", $columns ));
        }
        else{
            $select = array('SELECT *');
        }

        $select[] = sprintf( 'FROM `%s`', $table );

        if( null !== $where ){
            $select[]  = 'WHERE ' . $where;
        }

        return $this->fetchAll( join("\n", $select), $values );
    }

    /**
     * Fetch a single row from $tableName
     * Similar to 'select' but uses a LIMIT 1
     *
     * @param string $table Table to query
     * @param string $where Where clause
     * @param mixed $values Array or a single value
     * @param array $columns Optional array of collumns to fetch
     *
     *  @return StdClass $object
     */
    public function fetchRow( $table, $where = null, $values = null, $columns = null ){
        $values = (array) $values;

        if( null !== $columns ){
            $columns = array_map( 'sprintf', array_fill( 0, count($columns), '`%s`'), $columns );
            $select = array( 'SELECT ' . "\n" . join( ",\n", $columns ));
        }
        else{
            $select = array('SELECT *');
        }

        $select[] = sprintf( 'FROM `%s`', $table );

        if( null !== $where ){
            $select[]  = 'WHERE ' . $where;
        }

        $select[] = 'LIMIT 1';

        $result = $this->fetchAll( join( "\n", $select), $values );

        return array_shift( $result );
    }

    /**
     * Wrapper funciton for $sth->fetchAll. Prepares, executses and returns fetchAll
     *
     * @param $query Mysql query. Use ':key' or '?' for replacement
     * @param $values PDO ':key' => 'value' parameters
     * @param $style Fetch Style. defautls to 'objects'
     */
    public function fetchAll( $query, $values = null, $style = PDO::FETCH_OBJ, $options = null ){
        $values = (array) $values;

        $this->sth = $this->prepare( $query );
        $this->sth->execute( $values );

        if( isset( $options ) ){
            return $this->sth->fetchAll( $style, $options );
        }
        return $this->sth->fetchAll( $style );
    }


    /**
     * Wrapper function for simple INSERTS
     *
     * @param string $tableName     Table name to insert to
     * @param array $toInsert       Key/Value pairs to insert
     *
     * @return int $lastInsertId
     */
    public function insert( $table, $values ){
        $keys   = array();

        $columns = array_map(
            'sprintf',
            array_fill(0, count($values), '`%s`'),
            array_keys( $values )
        );

        $keys = array_map(
            'sprintf',
            array_fill(0, count($values), ':%s'),
            array_keys($values)
        );

        $values = array_combine( $keys, $values );

        $query = array();
        $query[] = 'INSERT INTO';
        $query[] = '`' . $table . '`';
        $query[] = '(' . join(',', $columns ) . ')';
        $query[] = 'VALUES';
        $query[] = '(' . join(',', $keys ) . ')';

        $this->sth = $this->prepare( join("\n", $query ) );

        $this->sth->execute( $values );

        return $this->lastInsertId();
    }

    /**
     * Write a single record
     *
     * @param string $table Table to write to
     * @param array $values Normal $key => $value pair.
     * @param mixed $where Columns to use in where clause. These are taken from $values
     *
     * @return int $id Last insert id
     */
    public function update( $table, $values, $where = 'id' ){
        $query  = array();
        $values = (array) $values;

        $where  = (array) $where;
        $where  = array_combine( $where, $where );
        $keys   = array_map(
            'sprintf',
            array_fill(0, count($values), ':%s'),
            array_keys($values)
        );

        foreach( $values as $key => $value ){
            if( !in_array( $key, $where ) ){
                $query[] = sprintf( '`%s` = :%s', $key, $key );
            }
        }

        $query  = array(sprintf('UPDATE `%s` SET %s', $table, join( ",\n", $query )));

        foreach( $where as $key => $value ){
            $where[$key] = sprintf( '`%s` = :%s', $key, $key);
        }

        $query[] = 'WHERE ' . join( ' AND ', $where );
        $values = array_combine( $keys, $values );

        $this->sth = $this->prepare( join("\n", $query ) );
        $this->sth->execute( $values );
        return $this->lastInsertId();
    }

    /**
     * Parse configuration options, inject some common defaults
     *
     * @param $config Configuration options see __construct
     * @return stdClass $config
     */
    private function parseConfig( array $config ){
        $template = array(
            'dbPassword'  => null,
            'dbDriver'    => 'mysql',
            'dbHost'      => '127.0.0.1'
        );

        $config = (object) array_merge( $template, $config );

        $config->dsn = sprintf(
            '%s:dbname=%s;host=%s',
            $config->dbDriver,
            $config->dbName,
            $config->dbHost
        );

        return $config;
    }
}
