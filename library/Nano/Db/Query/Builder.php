<?php
class Nano_Db_Query_Builder{
    private $_action = null;
    private $_from   = null;
    private $_where  = null;
    private $_update  = null;
    private $_group  = null;
    private $_order  = null;

    private $_selectColumns = null;

    private $_tableAlias    = array();
    private $_bindings      = array();
    private $_limitOffset   = array(null, null);
    private $_aliasPool     = null;

    public function __toString(){
        return $this->_buildSql();
    }

    public function select( $column ){
        $selectColumns = array();
        $columns       = $column;

        if( ! is_array( $column ) ){
            $columns = func_get_args();
        }

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

    public function delete(){
        $this->_action = 'delete';
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

    public function andWhere(){
        return $this;
    }

    public function orWhere(){
        return $this;
    }

    public function groupBy( $group ){
        $this->_group = func_get_args();
    }

    public function order(){
        return $this;
    }

    public function limit( $limit, $offset=0 ){
        $this->_limitOffset = array( $limit, $offset );

        return $this;
    }

    public function offset( $offset ){
        list( $limit, $oldOffset ) = $this->_limitOffset;
        $this->_limitOffset = array( $limit, $offset );

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
            case 'delete':
                $sql[] = 'DELETE';
        }

        $sql[] = $this->_buildFrom();
        $sql[] = $this->_buildWhere();
        $sql[] = $this->_buildGroup();
        $sql[] = $this->_buildLimitOffset();

        return join( "\n", array_filter($sql) );
    }

    private function _addBindings( $bindings ){
        if( ! is_array( $bindings ) ){
            $bindings = func_get_args();
        }

        $stack  = $bindings;

        while( $stack ){
            $value = array_shift( $stack );

            if( is_array( $value ) ){
                $stack += $value;
            }
            else{
                $this->_bindings[] = $value;
            }
        }
    }

    private function _setBindings( $bindings ){
        $this->_clearBindings();
        $this->_addBindings( $bindings );
    }

    private function _clearBindings(){
        $this->_bindings = array();
    }

    private function _buildUpdate(){

    }

    private function _buildWhere(){
        $whereClauses = (array) $this->_where;
        $where = array();
        $bindings = array();

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
                $bind = sprintf( '(%s)', join(',', $placeholders ));
                $where[] = sprintf('%s %s %s', $col, $op, $bind );
                $bindings[] = $value;
            }
            else if( ! in_array( $op, array( 'LIKE', '>', '<', '=', '!=', 'NOT' ) ) ){
                throw new Exception( 'Unsupported operator: ' . $op );
            }
            else if( ! is_scalar($value) ){//sanity check
                throw new Exception( 'Value must be a scalar: ' . $value );
            }
            else{
                $bindings[] = $value;
                //$this->_addBindings( $value );
                $where[] = sprintf('%s %s ?', $col, $op);
            }

        }
                //$this->_addBindings( $value );

        $this->_setBindings( $bindings );


        if( count( $where ) > 0 ){
            return 'WHERE ' . join( ' AND ', $where );
        }
    }

    private function _buildLimitOffset(){
        list( $limit, $offset ) = $this->_limitOffset;
        $sql = '';

        if( is_integer($limit) && $limit > 0 ){
            $sql = sprintf( 'LIMIT %d', $limit );
            if( is_integer($offset) && $offset > 0 ){
                $sql = sprintf( '%s OFFSET %d', $sql, $offset );
            }
        }


        return $sql;
    }

    private function _buildGroup(){
        $group = array();

        if( ! is_array( $this->_group ) ){
            return;
        }

        foreach( $this->_group as $clause ){
            $col    = $clause;
            $table  =  null;

            if( is_array( $clause ) ){
                if( isset($clause['table']) && isset($clause['col']) ){
                    $col   = $clause['col'];
                    $table = $clause['table'];
                }
            }
            else if( ! is_string( $clause ) ){
                continue;
            }

            $collect[] = $this->_buildTableCol( $col, $table );
        }

        return 'GROUP BY ' . join( ',', $collect );
    }

    private function _buildOrder(){
        $order = array();

        foreach( $this->_order as $clause ){
            if( is_array( $clause ) ){
                if( issset($clause['table']) && isset($clause['col']) ){
                    $collect[] = $this->_buildTableCol($clause['col'], $clause['table']);
                }
            }
            else if( is_string($clause) && in_array( $clause, array('RAND()')) ){
                $order[] = 'RAND()';
            }
            else if( is_string( $clause ) ){
                $order[] = sprintf('`%s`', $clause );
            }
        }

        return 'ORDER BY ' . join(',', $order );
    }

    private function _buildFrom(){
        $from    = (array) $this->_from;
        $collect = array();

        foreach( $from as $table ){
            if( $table instanceof Nano_Db_Query_Builder ){
                $alias = $this->_getTableAlias( (string) $table );
                $collect[] = sprintf('( %s ) %s', (string) $table, $alias );
            }
            else{
                $alias = $this->_getTableAlias( (string) $table );
                $collect[] = sprintf('`%s` %s', $table, $alias );
            }
        }

        return 'FROM ' . join( ",", $collect );

    }

    private function _buildSelect(){
        if( is_array( $this->_selectColumns ) ){
            $select = array();

            foreach( $this->_selectColumns as $selectColumn ){
                if( $selectColumn['table'] ){
                    $alias = $this->_getTableAlias( (string) $selectColumn['table'] );
                    $column = sprintf('%s.`%s`', $alias, $selectColumn['column'] );
                }

                if( $selectColumn['operator'] ){
                    $column = $this->_buildOperator(
                        $selectColumn['operator'], $selectColumn['column'] );
                }
                else{
                    $column   = sprintf('`%s`', $selectColumn['column']);
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

        $body = !empty($body) ? $body : 1;

        return sprintf( '%s(%s)', $op,  $body);
    }

    private function _buildTableCol( $col, $table = null ){
        if( $table ){
            $alias = $this->_getTableAlias( $table );
            return sprintf('%s.`%s`', $alias, $col );
        }
        return sprintf('`%s`', $col );
    }

    private function _getTableAlias( $tableName ){

        if( ! isset( $this->_tableAlias[$tableName] ) ){
            $this->_tableAlias[$tableName] = $this->_generateTableAlias();
        }

        return $this->_tableAlias[$tableName];
    }

    private function _generateTableAlias(){
        if( ! $this->_aliasPool ){
            $this->_aliasPool = range( 'a', 'z' );
        }

        return array_shift( $this->_aliasPool );
    }

    //private function _delete( $from, array $where ){
    //    list( $where_clause, $values ) = $this->_buildWhereClause( $where, $from );
    //
    //    $query = sprintf('DELETE FROM `%s` WHERE %s', $from, join( "AND", $where_clause ) );
    //    return $this->_saveExecute( $query, $values );
    //}
}
