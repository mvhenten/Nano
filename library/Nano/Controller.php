<?php
class Nano_Controller{
    private $_config;
    private $_request;
    private $_view;
    private $_layout;

    public function __construct( $request, $config ){
        $this->setRequest( $request );
        $this->setConfig( $config );

        $this->init();

        $this->preDispatch();

        try{
            call_user_func( array( $this, sprintf("%sAction", $request->action)));
        }
        catch( Exception $e ){
            die( sprintf('Action "%s" is not defined', $request->action) );
        }

        $this->postDispatch();
        $this->renderView();
    }

    protected function setConfig( Nano_Config $config ){
        $this->_config = $config;
    }

    protected function setRequest( Nano_Request $request ){
        $this->_request = $request;
    }

    protected function getConfig(){
        return $this->_config;
    }

    protected function getRequest(){
        return $this->_request;
    }

    protected function getLayout(){
        if( null == $this->_layout ){
            $this->_layout = 'default';
        }
        return $this->_layout;
    }

    protected function setView( $view ){
        $this->_view = $view;
    }

    protected function getView(){
        return $this->_view;
    }

    protected function setLayout( $name ){
        $this->_layout = $name;
    }

    protected function _redirect( $where, $how = 303 ){
        header( sprintf( 'Location: %s', $where, $how ));
        exit(1);
    }

    protected function _forward( $action, $controller = null ){
        if( null == $controller ){
            $controller = $this;
        }
        call_user_func(array($controller, sprintf('%sAction', ucfirst($action))));
    }


    protected function renderView(){ echo $this->getView(); }
    protected function preDispatch(){}
    protected function postDispatch(){}
    protected function init(){}
}
