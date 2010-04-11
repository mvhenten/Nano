<?php
class Nano_Template{
    private $template;
    private $values;

    public function __construct(){
        if( func_num_args() > 0 ){
            $this->setValues( func_get_args() );
        }
    }

    public function __toString(){
        $template = '';

        if( null !== $this->template ){
            if( is_callable( $this->template ) ){
                $template = call_user_func_array( $this->template, $this->getValues() );
            }
            else if( class_exists( $this->template ) ){
                $class = $this->template;
                $template = new $class( $this->getValues() );
            }
            else if( null !== ( $path =  $this->getTemplatePath() ) ){
                ob_start();
                @include( $path );
                $template = ob_get_clean();
            }
        }
		//text/html; charset=iso-8859-1
		header('Content-Type: text/html; charset=utf-8');

        return (string) $template;
    }

    public function setTemplate( $template ){
        $this->template = $template;
    }

    public function __get( $name ){
        return $this->getValues()->$name;
    }

    public function __set( $name, $value ){
        $this->getValues()->$name = $value;
    }

    private function getValues(){
        if( null == $this->values ){
            $this->values = new Nano_Collection();
        }

        return $this->values;
    }

    private function setValues( array $values ){
        foreach( $values as $name => $value ){
            $this->getValues()->$name = $value;
        }
    }

    /*
        Try to resolve a working template file path
    */
    private function getTemplatePath(){
        $basepath = str_replace( 'Nano/Template.php', '', __FILE__ );
        $template = trim( $this->template, '/' );

        if( file_exists( $this->template ) ){
            return $this->template;
        }
        else if( file_exists( '../' . $template ) ){
            return '../' . $template;
        }
        else if( file_exists( $basepath . $template ) ){
            return $basepath . $template;
        }
    }
}
