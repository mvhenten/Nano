<?php
/**
 * Response object; what comes after request.
 *
 * shoud be be renamed to http response
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


class Nano_Response {
    private $_content = array();
    private $_headers = array();
    private $_status = 200;

    /**
     *
     *
     * @param array   $args (optional)
     */
    public function __construct( array $args = array() ) {
        foreach ( $args as $key => $value ) {
            $this->__set( $key, $value );
        }
    }


    /**
     *
     *
     * @param unknown $key
     * @param unknown $value
     */
    public function __set( $key, $value ) {
        if ( ( $method = 'set' . ucfirst($key) ) && method_exists( $this, $method ) ) {
            $this->$method( $value );
        }
        else if ( ($member = "_$key") && property_exists( $this, $member ) ) {
                $this->$member = $value;
            }
    }


    /**
     *
     *
     * @return unknown
     */
    public function __toString() {
        return $this->toString();
    }


    /**
     *
     *
     * @return unknown
     */
    public function toString() {
        return join( "\n", $this->_content );
    }


    /**
     *
     */
    public function out() {
        $content = $this->_content;

        // N.B. This code checks for a toString method
        // instead of implicitly stringifying the object
        // __toString implicitly hides stacktraces
        foreach ( $content as $index => $item ) {
            if ( is_object($item) && method_exists( $item, 'toString' ) ) {
                $content[$index] = $item->toString();
            }
        }

        $content = join( "\n", $content );

        $this->addHeaders(
            //array( 'Expires: ' . date( 'r', strtotime('+1 Month', $inserted ) ) ),
            array( 'Content-Type:text/html; charset=UTF-8'),
            array( 'Cache-Control: max-age=36000, must-revalidate' ),
            array( 'Content-Length: ' . strlen($content), True ),
            array( 'Pragma: cache' )
        );

        foreach ( $this->_headers as $header ) {
            list( $string, $replace, $code ) = array_pad( (array) $header, 3, null );
            header( $string, $replace, $code );
        }

        echo $content;
    }


    /**
     *
     *
     * @param unknown $where
     * @param unknown $how   (optional)
     */
    public function redirect( $where, $how = 303 ) {
        header( sprintf( 'Location: %s', $where, $how ));
        exit(1);
    }


    /**
     *
     *
     * @param unknown $content
     * @return unknown
     */
    public function unshiftContent( $content ) {
        array_unshift( $this->_content, $content );
        return $this;
    }


    /**
     *
     *
     * @param unknown $content
     */
    public function push( $content ) {
        $this->pushContent( $content );
    }


    /**
     *
     *
     * @param unknown $content
     * @return unknown
     */
    public function pushContent( $content ) {
        $this->_content[] = $content;
        return $this;
    }


    /**
     *
     *
     * @param unknown $content
     * @return unknown
     */
    public function popContent( $content ) {
        array_pop( $this->_content[] );
        return $content;
    }


    /**
     *
     *
     * @param unknown $code (optional)
     * @return unknown
     */
    public function setStatus( $code = 200 ) {
        $this->_status = $code;
        return $this;
    }


    /**
     *
     *
     * @return unknown
     */
    public function getStatus() {
        if ( null == $this->_status ) {
            $this->setStatus( 200 );
        }

        return $this->_status;
    }


    /**
     *
     *
     * @param array   $headers (optional)
     * @return unknown
     */
    public function addHeaders( array $headers = array() ) {
        foreach ( $headers as $header ) {
            list($string, $replace, $status) = array_pad( (array) $header, 3, null );
            $this->addHeader( $string, $replace, $status );
        }
        return $this;
    }


    /**
     *
     *
     * @param unknown $string
     * @param unknown $replace (optional)
     * @param unknown $status  (optional)
     * @return unknown
     */
    public function addHeader( $string, $replace = True, $status = null ) {
        $this->_headers[] = array($string, $replace, $status);
        return $this;
    }


    /**
     *
     *
     * @param array   $header
     * @return unknown
     */
    public function setHeaders( array $header ) {
        $this->clearHeaders();

        foreach ( $headers as $header ) {
            $this->_headers[] = $header;
        }
        return $this;
    }


    /**
     *
     *
     * @param array   $content
     * @return unknown
     */
    public function setContent( array $content ) {
        $this->clear();
        foreach ( $content as $value ) {
            $this->_content[] = $value;
        }
        return $this;
    }


    /**
     *
     *
     * @return unknown
     */
    public function clear() {
        $this->_content = array();
        return $this;
    }


    /**
     *
     *
     * @return unknown
     */
    public function clearHeaders() {
        $this->_outputHeaders = array();
        return $this;
    }


}
