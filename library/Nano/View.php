<?php
/**
 * @file View.php
 *
 * Base class for a simple "view" part of the MVT. It generalises two
 * cases: call the appropriate function for get{YourAction} or simply 'get'
 * or 'post'. It keeps track of the template and response object, and tries
 * not to get in the way.
 *
 */
abstract class Nano_View{
    private $_response;
    private $_template;
    private $_request;


    public function __construct( Nano_Request $request, Nano_Collection $config ){
        $this->_request = $request;

        $action_pieces = explode( '_', $request->action );
        $method = join( '', array_map( 'ucfirst', $action_pieces ));

        if( ! method_exists( $this, $method ) ){
            if( $request->isPost() ){
                $method = 'post' . ucfirst($method);
            }
            else{
                $method = 'get' . ucfirst($method);
            }

            if( ! method_exists( $this, $method ) ){
                $method = $request->isPost() ? 'post' : 'get';
            }
        }

        $response = $this->$method( $request, $config );
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

    public function model( $name, $arguments = array() ){
        foreach( Nano_Autoloader::getNamespaces() as $ns => $val ){
            $class_name = sprintf('%s_Model_%s', ucfirst($ns), ucfirst($name));
            if( class_exists( $class_name )){
                return new $class_name( $arguments );
            }

            $class_name = sprintf('%s_Schema_%s', ucfirst($ns), ucfirst($name));
            if( class_exists( $class_name )){
                return new $class_name( $arguments );
            }
        }
        throw new Exception( "Unable to resolve $name" );
    }
}
