<?php
class Nano_Db_Query_Builder{
    private $_action = null;
    private $_from   = null;
    private $_where  = null;
    private $_group  = null;
    private $_order  = null;
    private $_limit  = null;

    private $_selectColumns = null;
    private $_tableAlias    = array();
    private $_bindings      = array();


    public function __toString(){
        return $this->_buildSql();
    }

    public function select( $column ){
        $selectColumns = array();

        $columns = func_get_args();

        foreach( $columns as $column ){
            if( ! is_array( $column ) ){
                $column = array( 'column' => $column );
            }
            $column = array_merge( array(
                'column'    => null,
                'table'     => null,
                'operator'  => null
            ), $column );

            $selectColumns[] = $column;
        }

        $this->_selectColumns = $selectColumns;
        $this->_action        = 'select';

        return $this;
    }

    public function from( $where ){
        if( is_array( $where ) ){
            $this->_from = $where;
        }
        else{
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
     */
    public function where( array $what ){
        $where = array();

        foreach( $what as $key => $value ){
            $clause = array(
                'table' => null,
                'col'   => null,
                'op'    => '=',
                'value' => null
            );

            if( ! is_numeric($key) ){
                $clause['col'] = $key;
                if( is_array( $value ) ){
                    list( $op, $nvalue ) = $value;
                    $clause['op']   = $op;
                    $value = $nvalue;
                }

                $clause['value'] = $value;
            }
            else if( is_array( $value ) ){
                $value = array_intersect_key( $value, $clause );
                $clause = array_merge( $clause, $value );
            }

            $where[] = $clause;
        }

        $this->_where = $where;
        return $this;
    }

    public function update(){
        return $this;
    }

    public function insert(){
        return $this;
    }

    public function delete(){
        return $this;
    }

    public function andWhere(){
        return $this;
    }

    public function orWhere(){
        return $this;
    }

    public function groupBy(){
        return $this;
    }

    public function order(){
        return $this;
    }

    public function limit(){
        return $this;
    }

    public function bindings(){
        return $this->_bindings;
    }

    private function _buildSql(){
        $sql = array();

        switch( $this->_action ){
            case 'select':
                $sql[] = $this->_buildSelect();
                break;
        }

        $sql[] = $this->_buildFrom();
        $sql[] = $this->_buildWhere();

        return join( "\n", $sql );
    }

    private function _addBindings( $bindings ){
        if( ! is_array( $bindings ) ){
            $bindings = func_get_args();
        }

        foreach( $bindings as $value ){
            $this->_bindings[] = $value;
        }
    }

    private function _setBindings( $bindings ){
        $this->_clearBindings();
        $this->_addBindings( $bindings );
    }

    private function _clearBindings(){
        $this->_bindings = array();
    }

    private function _buildWhere(){
        $whereClauses = (array) $this->_where;
        $where = array();

        foreach( $whereClauses as $clause ){
            extract( $clause ); //don't panic! scope! (col, op, value, table)

            if( $table ){
                $alias = $this->_getTableAlias( $table );
                $col   = sprintf( '`%s`.`%s`', $alias, $col );
            }
            else{
                $col    = sprintf( '`%s`', $col );
            }

            if( $op == 'IN' && is_array($value) ){
                $placeholders = array_fill( 0, count($value), '?' );
                $value = sprintf( '(%s)', join(',', $placeholders ));
                $this->_addBindings( $value );
            }
            else if( ! in_array( $op, array( 'LIKE', '>', '<', '=', '!=', 'NOT' ) ) ){
                throw new Exception( 'Unsupported operator: ' . $op );
            }
            else if( ! is_scalar($value) ){//sanity check
                throw new Exception( 'Value must be a scalar: ' . $value );
            }
            else{
                $this->_addBindings( $value );
            }

            $where[] = sprintf('%s %s %s', $col, $op, $value );
        }

        return 'WHERE ' . join( 'AND', $where );
    }

    private function _buildLimit(){
        $limit = $this->_limit;

        if( ! empty($limit) ){
            $limit_clause = (array) $limit;
            if( count($limit_clause) == 1 ){
                array_unshift($limit_clause, 0);
            }
        }

        return $limit_clause;
    }

    private function _buildFrom(){
        $from    = (array) $this->_from;
        $collect = array();

        foreach( $from as $table ){
            $alias = $this->_getTableAlias($table);
            $collect[] = sprintf('`%s` %s', $table, $alias );
        }

        return 'FROM ' . join( ",", $collect );

    }

    private function _buildSelect(){
        if( is_array( $this->_selectColumns ) ){
            $select = array();

            foreach( $this->_selectColumns as $selectColumn ){
                $column   = sprintf('`%s`', $selectColumn['column']);
                if( $selectColumn['table'] ){
                    $alias = $this->_getTableAlias( $selectColumn['table'] );
                    $column = sprintf('%s.%s', $alias, $column );
                }

                if( $selectColumn['operator'] ){
                    var_dump($column);
                    $column = $this->_buildOperator( $selectColumn['operator'], $column );
                }


                $select[] = $column;
            }

            return 'SELECT ' .  join( ',', $select );

        }

        return 'SELECT *';

    }

    private function _buildOperator( $op, $body ){
        $allowed = array('MIN', 'MAX', 'COUNT'); //keep it simple
        $op      = strtoupper( $op );

        if( ! in_array( $op, $allowed ) ){
            throw new Exception( sprintf('Operator not implemented: `%s`', $op ));
        }

        $body = isset($body) ? $body : 1;

        return sprintf( '%s(%s)', $op,  $body);
    }


    private function _getTableAlias( $tableName ){

        if( ! isset( $this->_tableAlias[$tableName] ) ){
            $this->_tableAlias[$tableName] = $this->_generateTableAlias();
        }

        return $this->_tableAlias[$tableName];
    }

    private function _generateTableAlias(){
        static $index = null;

        if( ! $index ){
            $index = range( 'a', 'z' );
        }

        return array_shift( $index );
    }

    //private function _delete( $from, array $where ){
    //    list( $where_clause, $values ) = $this->_buildWhereClause( $where, $from );
    //
    //    $query = sprintf('DELETE FROM `%s` WHERE %s', $from, join( "AND", $where_clause ) );
    //    return $this->_saveExecute( $query, $values );
    //}
}
