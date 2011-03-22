<?php
/**
 * @file Controller.php
 *
 * Base class for a simple "view" part of the MVT. It generalises two
 * cases: call the appropriate function for get{YourAction} or simply 'get'
 * or 'post'. It keeps track of the template and response object, and tries
 * not to get in the way.
 *
 */
abstract class Nano_Controller{
    private $_response;
    private $_template;
    private $_request;


    public function __construct( Nano_Request $request, Nano_Config $config ){
        $this->_request = $request;

        if( $request->isPost() ){
            $response = $this->post( $request, $config );
        }
        else{
            $method = sprintf('get%s', ucfirst($request->action));
            if( method_exists( $this, $method ) ){
                $response = $this->$method( $request, $config );
            }
            else{
                $response = $this->get( $request, $config );
            }
        }
        $this->response()->push($response);
    }

    /**
     * Handles all POST requests
     *
     * Every post request gets routed trough this function; you may want to use
     * _forward to finish processing in some other function.
     *
     * @param Nano_Request $request The request object
     * @param Nano_Config $config A nano_config object
     * @return void
     */
    protected function post( $request, $config ){
        return;
    }


    /**
     * Fallback handler for GET request
     *
     * You may define your own custom request hook like "get{Action}" for
     * specific request, or route all requests trough this function.
     *
     * @param Nano_Request $request
     * @param Nano_Config $config
     * @return void
     */
    protected function get( $request, $config ){
        return;
    }


    /**
     * Convenience method: parses a request object to determine a possibly valid
     * template name for a /module/xxx/controller/action style layout.
     *
     * @param Nano_Request $request A nano request object
     * @param string $base_path Relative template name.
     */
    //public function templatePath( $request = null, $base_path = 'template' ){
    //    $path = array_filter( array(
    //        ':module'       => $request->module,
    //        ':dir'          => $base_path,
    //        ':controller'   => $request->controller,
    //        ':action'       => $request->action
    //    ) );
    //
    //
    //    $path = join( '/', $path );
    //    return $path;
    //}

    public function response(){
        if( null == $this->_response ){
            $this->_response = new Nano_Response();
        }

        return $this->_response;
    }

    public function template(){
        if( null == $this->_template ){
            $this->_template = new Nano_Template(array('request'=>$this->request()));
        }

        return $this->_template;
    }

    public function request(){
        return $this->_request;
    }


    /**
     * Forward the entire request to a different action/controller
     * @param string $action Actual name of the action (whitout Action)
     * @param mixed $controller Controller name or object
     */
    //protected function forward( $action, $controller = null ){
    //    //@todo implement forward to different controller:
    //    // do we need to post-dspatch too?
    //    $request = $this->getRequest();
    //
    //    if( null == $controller ){
    //        $controller = $this;
    //    }
    //    else if ( is_string( $controller ) ){
    //        $controller = new $controller( $this->getRequest(), $this->getConfig() );
    //    }
    //    else if( !$controller instanceof Nano_Controller ){
    //        throw new Exception( 'Controller must be a propper class name or instance of Nano_Controller');
    //    }
    //
    //
    //    $controller->preDispatch();
    //
    //
    //    if( ($method = sprintf('%sAction', $action) )
    //       && method_exists($controller, $method) ){
    //        call_user_func( array( $this, sprintf("%sAction", $action)));
    //    }
    //    else{
    //        throw new Exception( sprintf('Action %s not defined', $action) );
    //    }
    //
    //    $this->postDispatch();
    //    $this->renderView();
    //}
}
