<?php
class Nano_View_Helper_Join extends Nano_View_Helper{
    function Join( $separator, $args ){
        $args = func_get_args();

        $separator = array_shift( $args );

        return join( $separator, $args );
    }
}
