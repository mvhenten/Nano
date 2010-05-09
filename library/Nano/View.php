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

    public function __construct( $config, Nano_Request $request ){
        $config = (array) $config;
        $config['request'] = $request;

        var_dump( $config ); exit;

        $this->setLayout( $config );
    }

    public function setLayout( array $layout ){
        foreach( $layout as $name => $value ){
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
