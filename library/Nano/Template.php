<?php
/**
 * Minimal Template loader. loads template files, and acts as a proxy/scope for
 * helper functions within template files.
 *
 */
if( ! defined( 'APPLICATION_PATH' ) )
    define( 'APPLICATION_PATH', '/' . trim( dirname( __FILE__ ), ' ./\\') );

class Nano_Template{
    protected $_request;
    protected $_helpers = array();
    protected $_values = array();
    protected $_templatePath = '';
    protected $_helperPath   = array(
        'Application/helper'
    );

    /**
     * Class constructor.
     * Reuqest object is not optional; other configuration parameters may be
     * given as initialization arguments.
     *
     * @param Nano_Reuqest $request A nano request object
     * @param array $config A key/value array
     */
    public function __construct( Nano_Request $request, array $config = array() ){
        $this->_request = $request;

        foreach( $config as $key => $value ){
            $this->__set( $key, $value );
        }
    }

    public function __set( $key, $value ){
        if( ($method = 'set' . ucfirst($key) ) && method_exists( $this, $method ) ){
            $this->$method( $value );
        }
        else if( ($member = "_$key") && property_exists( $this, $member ) ){
            $this->$member = $value;
        }
        else{
            $this->_values[$key] = $value;
        }
    }

    public function __get( $key ){
        if( ($member = "_$key") && property_exists( $this, $member ) ){
            return $this->$member;
        }
        else if( key_exists( $key, $this->_values ) ){
            return $this->_values[$key];
        }
    }

    /**
     * Templates operate within the scope of this class; helper functions may be
     * called as methods from this class
    */
    public function __call( $name, $arguments ){
        $helper = $this->getHelper( $name );
        return call_user_func_array( array($helper, $name), $arguments );
    }

    public function render( $template ){
        $path = $this->expandPath( $template );

        ob_start();
        require_once( $path );
        return ob_get_clean();
    }

    public function getRequest(){
        return $this->_request;
    }

    public function headScript(){
        return $this->getHelper( 'Script' );
    }

    public function getHelper( $name ){
        if( ! key_exists($name ,$this->_helpers)  ){
            $this->loadHelper( $name );
        }

        return $this->_helpers[strtolower($name)];
    }

    public function loadHelper( $name ){
        $name = ucfirst( $name );

        $klass = "Helper_" . $name;

        if( ! class_exists( $klass ) ){
            $basename = sprintf( "%s.php", $name );

            foreach( $this->_helperPath as $path ){
                $path = APPLICATION_PATH . '/' . $path . '/' . $basename;
                //print $path . "<br/>\n";
                if( file_exists( $path ) ){
                    require_once( $path );
                }
            }
        }
        if( ! class_exists( $klass ) ){
            $klass = 'Nano_View_Helper_' . $name;
        }

        if( class_exists( $klass ) ){
            $this->_helpers[strtolower($name)] = new $klass( $this );
        }
        else{
            //debug_print_backtrace();
            throw new Exception( "unable to resolve helper $name" );
        }
    }

    public function setValues( array $values = array() ){
        $this->_values = $values;
    }

    public function clearValues(){
        $this->_values = array();
    }

    public function addHelperPath( $path ){
        $this->_helperPath[] = $path;
    }

    public function setHelperPath( array $paths = array() ){
        $this->_helperPath = $paths;
    }

    private function expandPath( $name ){
        $name = trim( $name, ' /\\');
        $path = APPLICATION_PATH .
            sprintf( "%s/%s.phtml", trim( $this->_templatePath, ' /\\'), $name );
        return $path;
    }
}
