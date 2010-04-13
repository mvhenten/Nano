<?php
abstract class Nano_Bootstrap_Abstract{
    private $_view;

    public function __construct(){
        require_once( APPLICATION_PATH . '/lib/Autoloader.php');

        Nano_Autoloader::register();
        Nano_Autoloader::registerNamespace( 'Nano', APPLICATION_PATH . '/lib' );


        $config = new Nano_Config_Ini( APPLICATION_PATH . '/application.ini' );
        $router = new Nano_Router( $config->route );
        $request = new Nano_Request( $router );

        $this->_view = new Nano_Collection();

        Nano_Admin::setIdentity( array('id'=>1, 'name'=>"Admin"));

        //var_dump( $router->module );
        $name = sprintf( 'Controller_%s', ucfirst($request->controller ));

        if( null !== $router->module ){
            $base = ucfirst( $router->module );
            Nano_Autoloader::registerNamespace( $base, APPLICATION_PATH . '/' . $base );
            $name = sprintf( '%s_%s', ucfirst( $router->module ), $name );
        }
        else{
            $templatePath = APPLICATION_PATH . '/template';
            Nano_Autoloader::registerNamespace( 'Controller', APPLICATION_PATH . '/Controller' );
        }


        $class = new $name( $request );
        $view = $templatePath . sprintf('/%s/%s.phtml', $request->controller, $request->action );

        ob_start();
        include( $view );

        $this->_view->content = ob_get_clean();

        require_once( $templatePath . '/layout.phtml' );

    }

    public function __get( $name ){
        return $this->_view->$name;
    }

    private function categories(){
        $result = $this->posts( 'categories != ""', 'categories', array(), PDO::FETCH_COLUMN );
        if( is_array( $result ) && count( $result ) > 0 ){
            foreach( array_map( explode, array_fill( 0, count($result), ','), $result) as $i => $cat ){
                $cats = is_array($cats) ? array_merge( $cats, $cat ) : $cat;
            }
            return array_map('trim', array_unique(array_filter( $cats, is_scalar )));
        }
    }

    private function posts( $where = Null, $what = '*',
                            array $values = array(), $fetchMode = PDO::FETCH_OBJ ){
        $sql = sprintf( 'SELECT %s FROM post', $what );
        $sql = $where !== Null ? sprintf('%s WHERE %s', $sql, $where ) :
                                 sprintf('%s ORDER BY inserted DESC', $sql);
        $sth = $this->db->prepare( $sql );
        $sth->execute( $values );
        return $sth->fetchAll( $fetchMode );
    }
}
