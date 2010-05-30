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
    protected $_layoutpath;

    public function __construct( $config, $request ){
        $config = (array) $config;
        $config['request'] = $request;


        foreach( $config as $name => $value ){
            if( ($property = '_' . $name) && property_exists( $this, $property ) ){
                $this->$property = $value;
            }
        }
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

    public function getHelperPath(){
        if( null == $this->_helperPath ){
            $this->_helperPath = array();
        }
        return $this->_helperPath;
    }


    public function __toString(){
        if( false !== ( $path = $this->getViewScript() ) && ! empty($path) ){
            ob_start();
            require_once( $path );
            $this->getContent()->viewScript = ob_get_clean(); // content is now a string!
        }

        if( false !== ( $path = $this->getLayout() ) && ! empty( $path ) ){
            ob_start();
            require_once( $path );
            return ob_get_clean();
        }

        return '';
    }

    public function getLayout(){
        if( null === $this->_layout && null !== $this->_layoutpath ){
            $layout = sprintf('%s/%s/%s', $this->_base, $this->_path, $this->_layoutpath );
            $this->setLayout( $layout );
        }

        return $this->_layout;
    }

    public function disableLayout(){
        $this->_layout = false;
    }

    public function setLayout( $path ){
        $this->_layout = $path;
    }

    /**
     * Gets ViewScript filename
     */
    public function getViewScript(){
        if( null === $this->_view ){
            $request = $this->getRequest();

            $view = str_replace( array(':controller', ':action')
                , array( $request->controller, $request->action )
                , $this->_structure
            );

            $this->setViewScript( $view );
        }

        return $this->_view;
    }

    public function setViewScript( $view ){
        $path = sprintf( '%s/%s%s', $this->_base, $this->_path, $view );
        $this->_view = $path;
    }

    public function disableViewScript(){
        $this->_view = false;
    }

    private function getContent(){
        if( null === $this->_content ){
            $this->_content = new Nano_Collection();
        }
        return $this->_content;
    }

    public function getRequest(){
        return $this->_request;
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


    public function setHelperPath( $path ){
        if( null == $this->_helperPath ){
            $this->_helperPath = array();
        }

        $this->_helperPath[] = $path;
    }
}
