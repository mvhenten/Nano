<?php
class Nano_Request{
    private $_request;
    private $_post;
    private $_get;
    private $_router;
    private $_headers;

    public function __construct( Nano_Router $router = null ){
        if( null !== $router ){
            $this->_router = $router;
        }
    }

    public function __get( $name ){
        if( null !== $this->_router && null !== $this->_router->$name ){
            return $this->_router->$name;
        }
        elseif( null !== $this->getPost() && null !== $this->_post->$name ){
            return $this->_post->$name;
        }

        return $this->getRequest()->$name;
    }

    public function getRouter(){
        return $this->_router;
    }

    public function isXmlHttpRequest(){
        if( null == $this->_headers ){
            $this->_headers = apache_request_headers();
        }

        if( isset( $this->_headers['X-Requested-With'] ) ){
            if( $this->_headers['X-Requested-With'] == 'XMLHttpRequest' ){
                return true;
            }
        }
        return false;
    }

    public function isPost(){
        if( count( $_POST ) > 0 ){
            return true;
        }
        return false;
    }

    public function getPost(){
        if( $this->isPost() && null == $this->_post ){
            $this->_post = new Nano_Collection( $_POST );
        }

        return $this->_post;
    }

    public function getRequest(){
        if( null == $this->_request ){
            $this->_request = new Nano_Collection( $_REQUEST );
        }
        return $this->_request;
    }

    public function getRequestUri(){
        return $this->_router->getRequestUri();
    }

    public function url( array $url = array() ){
        if( empty($url) ){
            return $this->getRequestUri();
        }

        $base = (array) $this->getRouter();

        $route = array_merge( $base, $url );

        $base = array_intersect_key( $route, $base );//holds controller,etc
        $second = array_diff_key( $route, $base );//key value pairs url

        $module = isset( $base['module'] ) ? '/' . $base['module'] : '';
        unset( $base['module'] );

        foreach( $second as $key => $value ){
            $base[] = $key;
            $base[] = $value;
        }

        $url = $module . '/' . join("/", $base );

        return rtrim( $url, '/');
    }

    /**
     * borrowed from symfony
     */
    function slug( $text, $trim=64 ){
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        $text = trim($text, '-');

        if (function_exists('iconv')){
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)){
            return 'n-a';
        }

        return substr( $text, 0, $trim);
    }

}
