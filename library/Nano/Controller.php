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
    }

    public function dispatch(){
        $this->preDispatch();

        $request = $this->getRequest();

        if( ($method = sprintf('%sAction', $request->action) )
           && method_exists($this, $method) ){
            call_user_func( array( $this, sprintf("%sAction", $request->action)));
        }
        else{
            throw new Exception( sprintf('Action %s not defined', $request->action) );
        }

        $this->postDispatch();
        $this->renderView();
    }

    public function setLayout( $name ){
        $this->_layout = $name;
    }

    public function setHelperPath( $path ){
        $this->getView()->setHelperPath( $path );
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

    protected function setView( $layout, $router ){
        $view = new Nano_View( $layout, $router );

        $this->_view = $view;
    }

    protected function getView(){
        if( null == $this->_view ){
            $layout = 'default';

            //@todo this sets layout counter-intuitive.
            if( $this->getRequest()->module !== '' ){
                $layout = $this->getRequest()->module;
            }

            $layout = $this->getConfig()->layout[$layout];
            $request = $this->getRequest();

            $this->setView( $layout, $request->getRouter() );
        }

        return $this->_view;
    }

    protected function _pageNotFound( $content ){
        header("HTTP/1.0 404 Not Found", true, 404);
        echo $content;
        exit;
    }

    protected function _redirect( $where, $how = 303 ){
        header( sprintf( 'Location: %s', $where, $how ));
        exit(1);
    }

    protected function _forward( $action, $controller = null ){
        //@todo implement forward to different controller:
        // do we need to post-dspatch too?
        if( null == $controller ){
            $controller = $this;
        }

        $request = $this->getRequest();
        $layout = 'default';

        if( $request->module !== '' ){
            $layout = $request->module;
        }

        $layout = $this->getConfig()->layout[$layout];
        $router = $request->getRouter();


        $router->action = $action;

        $helperPath = $this->getView()->getHelperPath();

        $this->setView( $layout, $router );

        foreach( $helperPath as $path ){
            $this->getView()->setHelperPath( $path );
        }


        if( ($method = sprintf('%sAction', $request->action) )
           && method_exists($this, $method) ){
            call_user_func( array( $this, sprintf("%sAction", $request->action)));
        }
        else{
            throw new Exception( sprintf('Action %s not defined', $request->action) );
        }

        $this->postDispatch();
        $this->renderView();
        exit;
    }


    protected function renderView(){ echo $this->getView(); }
    protected function preDispatch(){}
    protected function postDispatch(){}
    protected function init(){}
}
