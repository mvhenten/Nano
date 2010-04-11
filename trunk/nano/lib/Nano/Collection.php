<?php
class Nano_Collection extends ArrayObject{
    public function __sconstruct( $args = null ){
        if( func_num_args() > 1 ){
            $args = func_get_args();
        }
        else if( ! is_array( $args ) ){
            $args = array( $args );
        }

        if( !empty( $args ) ){
            parent::__construct($args);
        }
        else{
            parent::__construct();
        }
    }

    public function __isset( $name ){
        return $this->offsetExists( $name );
    }

    public function __get( $name ){
        if( $this->offsetExists( $name ) ){
            return $this->offsetGet( $name );
        }
    }

    public function __set( $name, $value ){
        return $this->offsetSet( $name, $value );
    }

    public function map( $function ){
        if( func_num_args() > 1 ){
            $args = func_get_args();

            $function = array_shift( $args );
            $collect = array($function, (array) $this );

            foreach( $args as $value ){
                $collect[] = array_fill( 0, count($this), $value );
            }

            $collect = call_user_func_array( 'array_map', $collect );
        }
        else{
            $collect = array_map( $function, (array) $this ) ;
        }

        return new Nano_Collection( $collect );
    }

    public function join( $glue ){
        return join( $glue, (array) $this );
    }

    public function filter( $function = null ){
        return new Nano_Collection( array_filter( (array) $this, $function ) );
    }

    /**
     * remove $key from the collection
     *
     * @return mixed $element removed
     */
    public function remove( $key ){
        if( $this->offsetExists( $key ) ){
            $value = $this->offsetGet( $key );
            $this->offsetUnset( $key );
            return $value;
        }
    }

    /**
     * Adds an element end the beginning of the array
     */
    public function push( $element ){
        $this[] = $element;

        return $this;
    }

    /**
     * Return the first element of the collection
     * @return mixed $element
     */
    public function first(){
        $array = $this->toArray();
        return array_shift( $array );
    }

    /**
     * Return the last element of the collection
     * @return mixed $element
     */
    public function last(){
        $array = $this->toArray();
        return array_pop( $array );
    }

    /**
     * Returns a copy of the collection as an array
     * @return array $collection
     */
    public function toArray(){
        if( count( $this ) > 0 ){
            return (array) $this;
        }
        return array();
    }

    /**
     * Tries to re-route $name to array_$name,
     * passing the Collection itself as the first argument
     * @todo implement something better soon!
     */
    public function __call( $name, $arguments ){
        if( function_exists( 'array_' . $name ) ){
            if( $name == 'map' ){
                $arguments = array_merge(
                    array(array_shift($arguments), (array) $this),
                    $arguments
                );
            }
            else{
                $arguments = array_merge( array((array) $this), $arguments);
            }
            //echo "name exitst ";
            //var_dump( $arguments );

            return new Collection( call_user_func_array( 'array_' . $name, $arguments ));
        }
    }
}
