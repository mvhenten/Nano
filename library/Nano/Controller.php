<?php
class Nano_Controller{
    private $_config;
    private $_request;
    private $_view;
    private $_layout;

    private $_response;
    private $_template;

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

        $this->response()->out();
        //$this->renderTemplate();
        //$this->renderView();
    }

    public function setLayout( $name ){
        $this->_layout = $name;
    }

    //public function addHelperPath( $path ){
    //    $this->template()->addHelperPath();
    //}

    public function response(){
        if( null == $this->_response ){
            $this->_response = new Nano_Response();
        }

        return $this->_response;
    }

    public function template(){
        if( null == $this->_template ){
            $this->_template = new Nano_Template( $this->getRequest() );
        }

        return $this->_template;
    }


    //public function setHelperPath( $path ){
    //    $this->getView()->setHelperPath( $path );
    //}

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

    //protected function getLayout(){
    //    if( null == $this->_layout ){
    //        $this->_layout = 'default';
    //    }
    //    return $this->_layout;
    //}
    //
    //protected function setView( $layout, $request ){
    //    $view = new Nano_View( $layout, $request );
    //
    //    $this->_view = $view;
    //}

    //protected function getView(){
    //    if( null == $this->_view ){
    //        $layout = 'default';
    //
    //        //@todo this sets layout counter-intuitive.
    //        if( $this->getRequest()->module ){
    //            $layout = $this->getRequest()->module;
    //        }
    //
    //        $layout = $this->getConfig()->layout[$layout];
    //        $request = $this->getRequest();
    //
    //        $this->setView( $layout, $request );
    //    }
    //
    //    return $this->_view;
    //}

    public function _jsonOut( $data ){
        $this->response()->pushContent( json_encode( $data ) );
        $this->response()->addHeader( 'Content-type: application/json' );
        $this->response()->out();

        //
        //header('Cache-Control: no-cache, must-revalidate');
        //header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        //header('Content-type: application/json');
        //
        //echo json_encode( $data );
        //exit;
    }

    protected function _pageNotFound( $content ){
        $this->response()->setHeaders( array("HTTP/1.0 404 Not Found", true, 404) )
                        ->pushContent( $content )
                        ->out();
        exit;
    }

    protected function _redirect( $where, $how = 303 ){
        header( sprintf( 'Location: %s', $where, $how ));
        exit(1);
    }

    /**
     * Forward the entire request to a different action/controller
     * @param string $action Actual name of the action (whitout Action)
     * @param mixed $controller Controller name or object
     */
    protected function _forward( $action, $controller = null ){
        //@todo implement forward to different controller:
        // do we need to post-dspatch too?
        $request = $this->getRequest();

        if( null == $controller ){
            $controller = $this;
        }
        else if ( is_string( $controller ) ){
            $controller = new $controller( $this->getRequest(), $this->getConfig() );
        }
        else if( !$controller instanceof Nano_Controller ){
            throw new Exception( 'Controller must be a propper class name or instance of Nano_Controller');
        }


        $controller->preDispatch();


        if( ($method = sprintf('%sAction', $action) )
           && method_exists($controller, $method) ){
            call_user_func( array( $this, sprintf("%sAction", $action)));
        }
        else{
            throw new Exception( sprintf('Action %s not defined', $action) );
        }

        $this->postDispatch();
        $this->renderView();
    }

    protected function _helper( $name, $arguments ){
        $arguments = func_get_args();

        $name = array_shift( $arguments );

        return $this->getView()->__call( $name, $arguments );
    }


    protected function renderView(){ echo $this->getView(); }
    protected function preDispatch(){}
    protected function postDispatch(){}
    protected function init(){}
}
