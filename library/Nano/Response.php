<?php
/**
 * Response object; what comes after request.
 *
 * shoud be be renamed to http response
 */
class Nano_Response{
    private $_content = array();
    private $_headers = array();

    public function __construct( array $args = array() ){
        foreach( $args as $key => $value ){
            $this->__set( $key, $value );
        }
    }

    public function __set( $key, $value ){
        if( ( $method = 'set' . ucfirst($key) ) && method_exists( $this, $method ) ){
            $this->$method( $value );
        }
        else if( ($member = "_$key") && property_exists( $this, $member ) ){
            $this->$member = $value;
        }
    }

    public function __toString(){
        return join( "\n", $this->_content );
    }

    public function out(){
        $content = join( "\n", $this->_content );

        $this->addHeaders(
            //array( 'Expires: ' . date( 'r', strtotime('+1 Month', $inserted ) ) ),
            array( 'Cache-Control: max-age=36000, must-revalidate' ),
            array( 'Content-Length: ' . strlen($content), True ),
            array( 'Pragma: cache' )
        );

        foreach( $this->_headers as $header ){
            list( $string, $replace, $code ) = array_pad( (array) $header, 3, null );
            header( $string, $replace, $code );
            //call_user_func_array( 'header', $header );
        }
        echo $content;
    }

    public function unshiftContent( $content ){
        array_unshift( $this->_content, $content );
        return $this;
    }

    public function push( $content ){
        $this->pushContent( $content );
    }

    public function pushContent( $content ){
        $this->_content[] = $content;
        return $this;
    }

    public function popContent( $content ){
        array_pop( $this->_content[] );
        return $content;
    }


    public function setStatus( $code = 200 ){
        $this->_status = $code;
        return $this;
    }

    public function getStatus(){
        return $this->_status;
    }

    public function addHeaders( array $headers = array() ){
        foreach( $headers as $header ){
            list($string, $replace, $status) = array_pad( (array) $header, 3, null );
            $this->addHeader( $string, $replace, $status );
        }
        return $this;
    }

    public function addHeader( $string, $replace = True, $status = null ){
        $this->_headers[] = array($string, $replace, $status);
        return $this;
    }

    public function setHeaders( array $header ){
        $this->clearHeaders();

        foreach( $headers as $header ){
            $this->_headers[] = $header;
        }
        return $this;
    }

    public function setContent( array $content ){
        $this->clear();
        foreach( $content as $value ){
            $this->_content[] = $value;
        }
        return $this;
    }

    public function clear(){
        $this->_content = array();
        return $this;
    }

    public function clearHeaders(){
        $this->_outputHeaders = array();
        return $this;
    }
}
