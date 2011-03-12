<?php
class Nano_View_Helper_Ellipsize extends Nano_View_Helper{
    private $_values;

    public function Ellipsize( $words, $length=180, $ellipse='...', $strip = true){
        $this->_values = array($words,$length,$ellipse,$strip);
        return $this;
    }

    public function __toString(){
        list($words, $length, $ellipse, $strip) = $this->_values;

        if($strip){
            $words = strip_tags($words);
        }

        if( strlen($words) > $length ){
            $nwords = substr( $words, 0, $length );
            $pos = strrpos( $nwords, ' ');
            $words = substr( $words, 0, $pos ) . '...';
        }

        return $words;
    }
}
