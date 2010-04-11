<?php
/**
 * Mental exercise... how to add syntactic sugar to create_function?
 *
 */

class Sugar{
    public function __call( $function, $args ){
        call_user_func_array( array('F', 'call'), $args );
    }

    public static function __callStatic( $function, $args ){
        call_user_func_array( array('F', 'call'), $args );
    }
}

class F{
    static $args = array( 'echo "Hello World, $dude!<br/>";', array('dude' => 'Dude') );

    public function __construct(){
       self::$args= func_get_args();
    }

    public static function call(){
        if( func_num_args() === 0 && self::$args !== null ){
            //var_dump( self::$args );
            $args = self::$args;
        }
        else{
            $args = func_get_args();
        }
        call_user_func_array( array( 'self', 'exec' ), $args );
    }

    private static function exec( $code, $args ){
        $str = '';
        foreach( $args as $key => $value ){
            $str .= "$$key";
        }
        call_user_func_array( create_function( $str, $code ), array_values( $args ) );
    }
}

F::call();
new F( 'echo "Hello World, $name!<br/>";', array('name' => 'Matthijs') );
F::call();
F::call( 'echo "Hello World, $name!<br/>";', array('name' => 'Mister!') );

$s = new Sugar();

$s->F( 'echo "Hello World, $name!<br/>";', array('name' => 'My man!') );
Sugar::F( 'echo "Hello World, $name!<br/>";', array('name' => 'My man!') );
?>
