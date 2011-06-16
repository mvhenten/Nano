<?php
class Nano_Db_Adapter extends PDO {
    public function insert( $table, array $values ){
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

        $sth = $this->prepare( join("\n", $query ) );
        $this->_saveExecute( $query, $values );

        return $this->lastInsertId();
    }

    public function update( $table, array $fields, array $where ){
        $field_values = array();

        foreach( $fields as $key => $value ){
            $query_set = sprintf( '`%s` = ?', $key );
        }

        list( $where_key, $where_value ) = $where;

        $query_values = array_values( $fields );
        $query_values[] = $where_value;

        $sql = sprintf('
            UPDATE `%s`
            SET
                %s
            WHERE `%s` = :key
        ', $table,
            join( "", $query_set ),
            $where_key
        );

        $sth = $this->prepare( $sql );
        $this->_saveExecute( $query, $values );

        return $this->lastInsertId();
    }

    public function delete( $from, array $where ){
        list( $where_clause, $values ) = $this->_buildWhereClause( $where, $from );

        $query = sprintf('DELETE FROM `%s` WHERE %s', $from, join( "AND", $where_clause ) );
        return $this->_saveExecute( $query, $values );
    }

    public function select( array $what = array(), $from, array $where = array(), $limit = null ){
        $select_columns = $this->_buildSelectColumns( $what, $from );
        $limit_clause   = $this->_buildLimitClause( $limit );

        list( $where_clause, $values ) = $this->_buildWhereClause( $where, $from );

        $query = sprintf('SELECT %s FROM `%s`', join( ",\n", $select_columns ), $from );

        if( ! empty( $where_clause ) ){
            $query .= "\nWHERE " . join( "AND", $where_clause );
        }

        if( ! empty( $limit_clause ) ){
            list( $offset, $limit ) = $limit_clause;
            $query .= sprintf("\nLIMIT %d OFFSET %d", $limit, $offset );
        }

        return $this->_saveExecute( $query, $values );
    }

    private function _buildLimitClause( $limit ){
        $limit_clause = array();

        if( ! empty($limit) ){
            $limit_clause = (array) $limit;
            if( count($limit_clause) == 1 ){
                array_unshift($limit_clause, 0);
            }
        }

        return $limit_clause;
    }

    private function _buildSelectColumns( $what, $from ){
        $what = !empty($what) ? $what : array('*');
        $select_columns = array();

        foreach( $what as $column ){
            $select_columns[] = sprintf('`%s`.`%s`', $from, $column );
        }

        return $select_columns;
    }

    private function _buildWhereClause( $where, $from ){
        $where_clause = array();
        $where_values = array();

        foreach( $where as $key => $value ){
            $op = '=';

            if( is_array( $value ) ){
                list( $op, $value ) = $value;
            }

            if( ! in_array( $op, array( 'LIKE', '>', '<', '=', '!=', 'NOT' ) ) ){
                throw new Exception( 'Unsupported operator: ' . $op );
            }

            $where_clause[] = sprintf( '`%s`.`%s` %s ?', $from, $key, $op );
            $where_values[] = $value;
        }

        return array( $where_clause, $where_values );
    }

    private function _saveExecute( $query, $values ){
        $sth = $this->prepare( $query );

       if( false == $sth ){
            $error = print_r( $this->errorInfo(), true );
            throw new Exception( 'Query failed: PDOStatement::errorCode():' . $error );
        }
        else if( !$sth->execute( $values ) ){
            $error = print_r( $sth->errorInfo(), true );
            throw new Exception( 'Query failed: PDOStatement::errorCode():' . $error );
        }

        return $sth;
    }



}
