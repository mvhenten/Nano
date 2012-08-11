<?php
/**
 * library/Nano/Db/Query/Builder.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


class Nano_Db_Query_Builder {
    private $_action = null;
    private $_from   = null;
    private $_where  = null;
    private $_values  = null;
    private $_group  = null;
    private $_order  = null;

    private $_selectColumns = null;

    private $_tableAlias    = array();
    private $_bindings      = array();
    private $_limitOffset   = array(null, null);
    private $_aliasPool     = null;

    /**
     *
     *
     * @return unknown
     */
    public function __toString() {
        return $this->_buildSql();
    }


    /**
     *
     *
     * @param unknown $column
     * @return unknown
     */
    public function select( $column ) {
        $selectColumns = array();
        $columns       = $column;

        if ( ! is_array( $column ) ) {
            $columns = func_get_args();
        }


        foreach ( $columns as $key => $column ) {
            $operator = null;

            if ( ! is_numeric( $key ) ) {
                $operator = $key;
            }

            if ( ! is_array( $column ) ) {
                $column = array( 'column' => $column );
            }

            $column = array_merge( array(
                    'column'    => null,
                    'table'     => null,
                    'operator'  => $operator
                ), $column );

            $selectColumns[] = $column;
        }

        $this->_selectColumns = $selectColumns;
        $this->_action        = 'select';
        return $this;
    }


    /**
     *
     *
     * @param unknown $table
     * @param array   $values
     * @return unknown
     */
    public function update( $table, array $values ) {
        $update = array();

        foreach ( array_filter($values) as $key => $value ) {
            $update[] = array(
                'column' => $key,
                'value'  => $value,
                'table'  => $table,
            );
        }

        $this->_values = $update;
        $this->_action = 'update';
        return $this;
    }


    /**
     *
     *
     * @param unknown $table
     * @param array   $values
     * @return unknown
     */
    public function insert( $table, array $values ) {
        $insert = array();
        $values = array_filter( $values );

        if ( empty( $values ) ) { // hard assertion: no empty arrays!
            throw new Exception( 'insert must have at least values' );
        }

        foreach ( $values as $key => $value ) {
            $insert[] = array(
                'column' => $key,
                'value'  => $value,
                'table'  => $table,
            );
        }

        $this->_values = $insert;
        $this->_action = 'insert';
        return $this;
    }


    /**
     *
     *
     * @param unknown $table
     * @return unknown
     */
    public function delete( $table ) {
        $this->_from = array( $table );
        $this->_action = 'delete';
        return $this;
    }


    /**
     *
     *
     * @param unknown $where
     * @return unknown
     */
    public function from( $where ) {
        if ( is_array( $where ) ) {
            $this->_from = $where;
        }
        else {
            $this->_from = func_get_args();
        }

        return $this;
    }


    /**
     *  $where = array(
     *      'id'    => 1,
     *      'id'    => array( 'IN', array(1,2,3) ),
     *      array( 'table' => 'foo', 'col' => 'id', 'op' => '>', 'value' => 1 )
     *     );
     *
     * @param array   $what
     * @return unknown
     */
    public function where( array $what ) {
        $where = array();

        foreach ( $what as $key => $value ) {
            $clause = array(
                'table' => null,
                'col'   => null,
                'op'    => '=',
                'value' => null
            );

            if ( is_numeric( $key ) && is_array($value) ) {
                $value = array_intersect_key( $value, $clause );
                $clause = array_merge( $clause, $value );
            }
            else {
                $clause['col'] = $key;
                $clause['value'] = $value;

                if ( is_array( $value ) ) {
                    $clause['op'] = 'IN';
                }
            }

            $where[] = $clause;
        }


        $this->_where = $where;
        return $this;
    }


    /**
     *
     *
     * @param unknown $order
     */
    public function order( $order ) {
        $this->_order = func_get_args();
    }


    /**
     *
     *
     * @param unknown $group
     */
    public function group( $group ) {
        $this->_group = func_get_args();
    }


    /**
     *
     *
     * @param unknown $group
     */
    public function groupBy( $group ) {
        $this->_group = func_get_args();
    }


    /**
     *
     *
     * @param unknown $limit
     * @param unknown $offset (optional)
     * @return unknown
     */
    public function limit( $limit, $offset=0 ) {
        $this->_limitOffset = array( $limit, $offset );
        return $this;
    }


    /**
     *
     *
     * @param unknown $offset
     * @return unknown
     */
    public function offset( $offset ) {
        list( $limit, $oldOffset ) = $this->_limitOffset;
        $this->_limitOffset = array( $limit, $offset );
        return $this;
    }


    /**
     *
     *
     * @return unknown
     */
    public function bindings() {
        return array_values($this->_bindings);
    }


    /**
     *
     *
     * @return unknown
     */
    private function _buildSql() {
        $sql = array();
        $this->_clearBindings();

        $method = '_build' . ucfirst($this->_action);
        $sql[] = $this->$method();

        if ( $this->_action == 'delete' ) {
            $sql[] = $this->_buildFrom();
            $sql[] = $this->_buildWhere();
        }
        else if ( $this->_action != 'insert' && $this->_action != 'update' ) {
                $sql[] = $this->_buildFrom();
                $sql[] = $this->_buildWhere();
                $sql[] = $this->_buildGroup();
                $sql[] = $this->_buildOrder();
                $sql[] = $this->_buildLimitOffset();
            }

        if ( $this->_action == 'update' ) {
            $sql[] = $this->_buildWhere();
        }

        return join( "\n", array_filter($sql) );
    }


    /**
     *
     *
     * @param unknown $bindings
     */
    private function _addBindings( $bindings ) {
        if ( ! is_array( $bindings ) ) {
            $bindings = func_get_args();
        }


        $this->_bindings = array_merge($this->_bindings, $bindings);
    }


    /**
     *
     *
     * @param unknown $bindings
     */
    private function _setBindings( $bindings ) {
        $this->_clearBindings();
        $this->_addBindings( $bindings );
    }


    /**
     *
     */
    private function _clearBindings() {
        $this->_bindings = array();
    }


    /**
     *
     *
     * @return unknown
     */
    private function _buildWhere() {
        $whereClauses = (array) $this->_where;
        $where = array();
        $bindings = array();

        foreach ( $whereClauses as $clause ) {
            extract( $clause ); //don't panic! scope! (col, op, value, table)

            if ( $table ) {
                $alias = $this->_getTableAlias( $table );
                $col   = sprintf( '`%s`.`%s`', $alias, $col );
            }
            else {
                $col    = sprintf( '`%s`', $col );
            }

            if ( $op == 'IN' && is_array($value) ) {
                $placeholders = array_fill( 0, count($value), '?' );
                $bind = sprintf( '(%s)', join(',', $placeholders ));
                $where[] = sprintf('%s %s %s', $col, $op, $bind );
                $bindings = array_merge( $bindings, $value );
            }
            else if ( ! in_array( $op, array( 'LIKE', '>', '<', '=', '!=', 'NOT' ) ) ) {
                    throw new Exception( 'Unsupported operator: ' . $op );
                }
            else if ( ! is_scalar($value) ) {//sanity check
                    throw new Exception( 'Value must be a scalar: ' . $value );
                }
            else {
                $bindings[] = $value;
                $where[] = sprintf('%s %s ?', $col, $op);
            }

        }

        $this->_addBindings( $bindings );


        if ( count( $where ) > 0 ) {
            return 'WHERE ' . join( ' AND ', $where );
        }
    }


    /**
     *
     *
     * @return unknown
     */
    private function _buildLimitOffset() {
        list( $limit, $offset ) = $this->_limitOffset;

        $sql = '';

        if ( is_integer($limit) && $limit > 0 ) {
            $sql = sprintf( 'LIMIT %d', $limit );
            if ( is_integer($offset) && $offset > 0 ) {
                $sql = sprintf( '%s OFFSET %d', $sql, $offset );
            }
        }

        return $sql;
    }


    /**
     *
     *
     * @return unknown
     */
    private function _buildGroup() {
        $group = array();

        if ( ! is_array( $this->_group ) ) {
            return;
        }

        foreach ( $this->_group as $clause ) {
            $col    = $clause;
            $table  =  null;

            if ( is_array( $clause ) ) {
                if ( isset($clause['table']) && isset($clause['col']) ) {
                    $col   = $clause['col'];
                    $table = $clause['table'];
                }
            }
            else if ( ! is_string( $clause ) ) {
                    continue;
                }

            $collect[] = $this->_buildTableCol( $col, $table );
        }

        return 'GROUP BY ' . join( ',', $collect );
    }


    /**
     *
     *
     * @return unknown
     */
    private function _buildOrder() {
        $order = array();

        if ( null == $this->_order ) return;

        foreach ( $this->_order as $clause ) {
            if ( is_array( $clause ) ) {
                if ( isset($clause['table']) && isset($clause['col']) ) {
                    $cols = (array) $clause['col'];
                    foreach ( $cols as $col ) {
                        $order[] = $this->_buildTableCol( $col, $clause['table']);
                    }
                }
            }
            else if ( is_string($clause) && in_array( $clause, array('RAND()')) ) {
                    $order[] = 'RAND()';
                }
            else if ( is_string( $clause ) && preg_match('/([-+])?(\w+)/', $clause, $match ) ) {
                    list( , $op, $s_clause ) = $match;
                    $order[] = sprintf('%s`%s`', $op, $s_clause );
                }
        }

        if ( count($order) == 0 ) return '';

        return 'ORDER BY ' . join(',', $order );
    }


    /**
     *
     *
     * @return unknown
     */
    private function _buildFrom() {
        $from    = (array) $this->_from;
        $collect = array();

        foreach ( $from as $table ) {
            $alias     = $this->_getTableAlias( (string) $table );
            $collect[] = sprintf('`%s` %s', $table, $alias );
        }

        if ( count($from) == 0 ) return '';
        return 'FROM ' . join( ",", $collect );
    }


    /**
     *
     *
     * @return unknown
     */
    private function _buildSelect() {
        if ( is_array( $this->_selectColumns ) ) {
            $select = array();

            foreach ( $this->_selectColumns as $selectColumn ) {
                if ( $selectColumn['table'] ) {
                    $alias = $this->_getTableAlias( (string) $selectColumn['table'] );
                    $column = sprintf('%s.`%s`', $alias, $selectColumn['column'] );
                }

                if ( $selectColumn['operator'] ) {
                    $column = $this->_buildOperator(
                        $selectColumn['operator'], $selectColumn['column'] );
                }
                else {
                    $column   = sprintf('`%s`', $selectColumn['column']);
                }


                $select[] = $column;
            }

            return 'SELECT ' .  join( ',', $select );
        }

        return 'SELECT *';
    }


    /**
     *
     *
     * @return unknown
     */
    private function _buildDelete() {
        return 'DELETE';
    }


    /**
     *
     *
     * @return unknown
     */
    private function _buildUpdate() {
        $update   = array();
        $bindings = array();

        foreach ( $this->_values as $col ) {
            $column = $col['column'];
            $table  = $col['table'];

            $update[] = sprintf('`%s` = ?',  $column );
            $bindings[] = $col['value'];
        }

        $table = $this->_values[0]['table'];

        $this->_addBindings( $bindings );
        return sprintf("UPDATE `%s` SET\n%s", $table, join( ",\n", $update ) );
    }


    /**
     *
     *
     * @return unknown
     */
    private function _buildInsert() {
        $columns   = array();
        $bindings = array();

        foreach ( $this->_values as $col ) {
            $columns[]  = '`' . $col['column'] . '`';
            $bindings[] = $col['value'];
        }

        $table = $this->_values[0]['table'];

        $this->_addBindings( $bindings );
        $values = array_fill( 0, count($bindings), '?' );


        return sprintf('INSERT INTO `%s` ( %s ) VALUES ( %s )',
            $table, join(',', $columns), join(',', $values)
        );
    }


    /**
     *
     *
     * @param unknown $op
     * @param unknown $body
     * @return unknown
     */
    private function _buildOperator( $op, $body ) {
        $allowed = array('MIN', 'MAX', 'COUNT'); //keep it simple
        $op      = strtoupper( $op );

        if ( ! in_array( $op, $allowed ) ) {
            throw new Exception( sprintf('Operator not implemented: `%s`', $op ));
        }

        $body = !empty($body) ? $body : 1;

        return sprintf( '%s(%s)', $op,  $body);
    }


    /**
     *
     *
     * @param unknown $col
     * @param unknown $table (optional)
     * @return unknown
     */
    private function _buildTableCol( $col, $table = null ) {
        if ( $table ) {
            $alias = $this->_getTableAlias( $table );
            return sprintf('%s.`%s`', $alias, $col );
        }
        return sprintf('`%s`', $col );
    }


    /**
     *
     *
     * @param unknown $tableName
     * @return unknown
     */
    private function _getTableAlias( $tableName ) {

        if ( ! isset( $this->_tableAlias[$tableName] ) ) {
            $this->_tableAlias[$tableName] = $this->_generateTableAlias();
        }

        return $this->_tableAlias[$tableName];
    }


    /**
     *
     *
     * @return unknown
     */
    private function _generateTableAlias() {
        if ( ! $this->_aliasPool ) {
            $this->_aliasPool = range( 'a', 'z' );
        }

        return array_shift( $this->_aliasPool );
    }


}
