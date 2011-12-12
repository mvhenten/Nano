<?php
/**
 * Nano's Simple Template Engine. The template class provides two things:
 *
 *  1) an inheritance based template engine based on similar concepts as Django's
 *      templating engine from the python world
 *  2) an execution scope for templates; variables within the template can be accessed
 *      as members of this class, and helper classes can be accessed as methods of this class
 */
if( ! defined( 'APPLICATION_ROOT' ) ){
    $nano_root = dirname( __FILE__ );
    define( 'APPLICATION_ROOT', dirname($nano_root) );
}

class Nano_Template{
    protected $_parents = array();
    protected $_blocks  = array();

    protected $_helpers = array();
    protected $_values = array();
    protected $_templatePath = '';
    protected $_helperPath   = array('helper');

    private $_templates = array();

    /**
     * Class constructor.
     * Reuqest object is not optional; other configuration parameters may be
     * given as initialization arguments.
     *
     * @param Nano_Reuqest $request A nano request object
     * @param array $config A key/value array
     */
    public function __construct( array $config = array() ){
        foreach( $config as $key => $value ){
            $this->__set( $key, $value );
        }
    }

    /**
     * Any values you need to carry over to the template are proxied trough
     * magical __set and __get
     */
    public function __set( $key, $value ){
        if( ($method = 'set' . ucfirst($key) ) && method_exists( $this, $method ) ){
            $this->$method( $value );
        }
        else if( ($member = "_$key") && property_exists( $this, $member ) ){
            $this->$member = $value;
        }
        else{
            $this->_values[$key] = $value;
        }
    }

    /**
     * Any values you need to carry over to the template are proxied trough
     * magical __set and __get
     */
    public function __get( $key ){
        if( ($member = "_$key") && property_exists( $this, $member ) ){
            return $this->$member;
        }
        else if( key_exists( $key, $this->_values ) ){
            return $this->_values[$key];
        }
    }

    /**
     * The template class acts as a proxy for helper classes. A helper class may
     * be factored by calling it's short name as a method of the current template
     * class. The template class then takes care of loading and instantiating
     * the helper.
     *
     * @param string $name Helper name, like 'url' for the url helper
     * @param mixed $arguments Optional creation arguments for url helpers
     *
     * @return void
    */
    public function __call( $name, $arguments ){
        $helper = $this->getHelper( $name );
        return call_user_func_array( array($helper, $name), $arguments );
    }

    public function __toString(){
        return $this->toString();
    }

    public function toString(){
        $collect = '';

        foreach( $this->_templates as $template ){
            $this->_parents = array( $template );

            while( count( $this->_parents ) > 0 ){
                $tpl = array_pop( $this->_parents );
                $content = $this->_include( $tpl, $this->_values );
            }

            $collect .= $content;
        }

        return $collect;
    }

    public function process( $tpl ){
        echo $this->_include( $tpl, $this->_values );
    }

    /**
     * Should have been called include ( but it cannot )
     *
     * Integrates a template file with limited scope
     *
     */
    public function integrate( $tpl, $scope_values = null ){
        $scope_values = func_get_args();
        $tpl = array_shift( $scope_values );
        echo $this->_include( $tpl, $scope_values );
    }

    private function _include( $tpl_name, $scope_values ){
        extract( $scope_values, EXTR_SKIP&EXTR_REFS  );

        $tpl_relative_path = preg_replace( '/^' . str_replace( '/', '\/', APPLICATION_PATH ) . '/', '', $tpl_name );
        $tpl_absolute_path = $this->expandPath( $tpl_relative_path );

        ob_start();
        ini_set( 'log_errors', 1 );
        ini_set( 'display_errors', 0);
        include( $tpl_absolute_path );

        return ob_get_clean();
    }

    /**
     * Renders the template; also cascades up to the templates
     * this template may inherit from. Template names may be in the form of
     * :modulename/:templatepath/:actionpath/:templatename, but without any
     * file suffix and relative to the project directory
     *
     * @param $name Simple template name
     * @return string $rendered_output Rendered output
     */
    public function render( $name ){
        $this->_templates[] = $name;

        return $this;
    }

    /**
     * Demarcates the start of a named content block in a template.
     * Note that only the top parent template can render content outside
     * of block regions.
     *
     * @param string $name Name of the content region, such as "title", "navbar"
     */
    public function block( $name ){
        ob_start();
        if( ! key_exists( $name, $this->_blocks ) ){
            $this->_blocks[$name] = array();
        }
    }

    /**
     * Announces the end of a named content block, adds the accumulated buffer
     * to the _blocks array and flushes buffer.
     *
     * N.B. you must properly close each content region.
     * N.B. This function produces output, altough during render output is buffered
     *
     * @param string $name Name of the content region
     * @param bool $append=True Should content be inserted at the start or appended
     * @return void
     */
    public function endBlock( $name, $append=True ){
        $content = ob_get_clean();

        if( $append ){
            array_unshift( $this->_blocks[$name], $content );
        }
        else{
            $this->_blocks[$name][] = $content;
        }

        $string = join( "\n", array_reverse($this->_blocks[$name]) );
        echo $string;
    }

    /**
     * Adds a parent template to the list of templates that must be rendered
     * @param string $name Relative template name. full path and suffix are added
     */
    public function wrapper( $name ){
        $this->_parents[] = $name;
    }

    /**
     * Expands a template name into a template path; e.g. call templates by
     * using a relative path starting at the application root.
     *
     * @param string $name Basic name, like 'edit'. Omit file suffix. Nested names can be page/edit
     * @return string $path Full path to /APPLICATION/$name.phtml
     */
    private function expandPath( $name ){
        $name = trim( $name, ' /\\');

        //$path = array(trim( $this->_templatePath, ' /\\'), );
        //$path = join( '/', array_filter($path));
        //return APPLICATION_PATH . '/' . $path;

        $path = join( '/', array_filter( array(
            APPLICATION_PATH,
            trim( $this->_templatePath, ' /\\'),
            $name . '.phtml'
        )));

        return $path;
    }

    public function templateExists( $template ){
        $path = $this->expandPath( $template );

        return file_exists( $path );
    }

    /**
     * Wrapper around the script helper
    */
    public function headScript(){
        return $this->getHelper( 'Script' );
    }

    /**
     * Retrieves a helper class based on the single name for that helper
     *
     * @param string $name Plain name; e.g. Nano_Helper_Script becomes 'script'
     * @return Nano_Helper $helper
     */
    public function getHelper( $name ){

        if( ! key_exists(strtolower($name) ,$this->_helpers)  ){
            $this->loadHelper( $name );
        }

        return $this->_helpers[strtolower($name)];
    }

    /**
     * Performs a lookup, and instantiates a helper class with the simple name $name.
     * e.g. 'script' will be expanded to match either 'Helper_Script' or even 'Nano_View_Helper'
     *
     * Classes that can be accessed trough $_helperPath will get precedence over
     * classes from Nano_View_Helper_*
     *
     * @param string $name Simple helper name, e.g. like 'url'
     * @return void
     */
    public function loadHelper( $name ){
        $name   = ucfirst( $name );
        $helper = null;

        foreach( Nano_Autoloader::getNamespaces() as $ns => $path ){
            $klass = $ns . "_Helper_" . $name;

            if( !class_exists( $klass ) ){
                continue;
            }
            break;
        }

        if( ! class_exists( $klass ) ){
            $klass = 'Nano_View_Helper_' . $name;
        }

        if( class_exists( $klass ) ){
            $this->_helpers[strtolower($name)] = new $klass( $this );
        }
        else{
            throw new Exception( "Unable to resolve helper $name" );
        }
    }

    /** various getters and setters **/

    public function setValues( array $values = array() ){
        $this->_values = $values;
    }

    public function getValues(){
        return $this->_values;
    }

    public function clearValues(){
        $this->_values = array();
    }

    public function addHelperPath( $path ){
        $this->_helperPath[] = $path;
    }

    public function setHelperPath( array $paths = array() ){
        $this->_helperPath = $paths;
    }

    public function setTemplate( $template ){
        $this->_templates = (array) $template;
    }

    public function setTemplates( $templates ){
        $this->_templates = (array) $template;
    }

    //public function getRequest(){
    //    return $this->_request;
    //}
}
