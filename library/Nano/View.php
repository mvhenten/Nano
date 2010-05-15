<?php
/**
 * basic attempt at a view abstraction.
 * This implementation is far from generic enough but serves the purpose of
 * being a placeholder for now!
 */
class Nano_View{
    protected $_base;
    protected $_path;
    protected $_layout;
    protected $_structure;
    protected $_request;
    protected $_view;
    protected $_content;
    protected $_helperPath;
    protected $_helpers;

    public function __construct( $config, $request ){
        $config = (array) $config;
        $config['request'] = $request;

        $this->setLayout( $config );
    }

    public function __set( $name, $value ){
        $this->getContent()->$name = $value;
    }

    public function __get( $name ){
        return $this->getContent()->$name;
    }

    public function __call( $name, $arguments ){
        $helper = $this->getHelper( $name );
        return call_user_func_array( array($helper, $name), $arguments );
    }

    public function getHelper( $name ){
        if( null == $this->_helpers ){
            $this->_helpers = array();
        }

        if( !in_array( $name, $this->_helpers ) ){
            $base       = ucfirst( $name ) . '.php';
            $paths      = $this->getHelperPath();
            $className  = 'Helper_' . ucfirst($name);
            $helper     = null;

            foreach( $paths as $path ){
                $path = $path . '/' . $base;
                if( file_exists( $path ) ){
                    require_once( $path );
                    if( class_exists( $className ) ){
                        $helper = new $className;
                    }
                }
            }

            if( null == $helper ){
                $className = 'Nano_View_Helper_' . ucfirst($name);
                // ok this will throw an exception
                // but you should know this...
                $helper = new $className;
            }

            $this->_helpers[$name] = $helper;
        }

        return $this->_helpers[$name];
    }

    public function setLayout( array $layout ){
        foreach( $layout as $name => $value ){
            if( ($property = '_' . $name) && property_exists( $this, $property ) ){
                $this->$property = $value;
            }
        }
    }

    public function setHelperPath( $path ){
        if( null == $this->_helperPath ){
            $this->_helperPath = array();
        }

        $this->_helperPath[] = $path;
    }

    public function getHelperPath(){
        if( null == $this->_helperPath ){
            $this->_helperPath = array();
        }
        return $this->_helperPath;
    }


    public function __toString(){
        $layout = sprintf('%s/%s/%s', $this->_base, $this->_path, $this->_layout );

        $view = str_replace( array(':controller', ':action')
            , array( $this->getRequest()->controller, $this->getRequest()->action )
            , $this->_structure
        );

        $path = sprintf( '%s/%s%s', $this->_base, $this->_path, $view );


        ob_start();
        require_once( $path );
        $this->_view = ob_get_clean();

        ob_start();
        include( $layout );

        return ob_get_clean();
    }

    private function getContent(){
        if( null == $this->_content ){
            $this->_content = new Nano_Collection();
        }
        return $this->_content;
    }

    private function getView(){
        return $this->_view;
    }

    public function getRequest(){

        return $this->_request;
    }
}
