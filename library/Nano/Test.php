<?php
class Nano_Test {
    private $_fg_colors = array(
        'black'         => '0;30',
        'dark_gray'     => '1;30',
        'blue'          => '0;34',
        'light_blue'    => '1;34',
        'green'         => '0;32',
        'light_green'   => '1;32',
        'cyan'          => '0;36',
        'light_cyan'    => '1;36',
        'red'           => '0;31',
        'light_red'     => '1;31',
        'purple'        => '0;35',
        'light_purple'  => '1;35',
        'brown'         => '0;33',
        'yellow'        => '1;33',
        'light_gray'    => '0;37',
        'white'         => '1;37'

    );

    private $_bg_colors = array(
        'black'         => '40',
        'red'           => '41',
        'green'         => '42',
        'yellow'        => '43',
        'blue'          => '44',
        'magenta'       => '45',
        'cyan'          => '46',
        'light_gray'    => '47'
    );

    private $_test_count = 0;

    public function __construct(){
        $this->_run();
    }

    private function _run(){
        global $argc, $argv;

        $name = get_class( $this );
        $tests = array_diff( get_class_methods( $name ), get_class_methods( 'Nano_Test' ) );

        foreach( $tests as $method ){
            $this->log( ">>> running test: $name::$method" );
            $start = microtime( true );
            call_user_method( $method, $this, array( $argc, $argv ));
            $this->log( sprintf( ">>> Ran %d tests in %0.3fms",
                $this->_test_count, microtime(true) - $start ));
            $this->_test_count = 0;
        }

        exit(0);
    }

    protected function ok( $message ){
        $this->info( $message, $this->_test_count . '. ', 'light_blue' );
        $this->_test_count +=1;
    }

    protected function block( $str, $color='white' ){
        $str = wordwrap( $str, 70 );
        $lines = explode( "\n", $str );


        $this->line( '=', 80 );

        foreach( $lines as $line ){
            $line = $this->mb_str_pad( $line, 76 );
            $this->info(  $line . '|', '| ', $color );
        }

        $this->line( '=', 80 );
        $this->line();
    }


    protected function dump( $var ){
        $lines = explode( "\n", print_r( $var, true ));
        foreach( $lines as $line ){
            $this->info( $line, 'D: ', 'white');
        }
    }

    protected function warn( $str ){
        $this->info( $str, ' W: ', 'red');
    }

    protected function error( $str ){
        $this->info( $str, ' E: ', 'light_red');
    }

    protected function info( $str, $pfx = 'I: ', $color = 'green' ){
        $str = sprintf( " %s %s", $pfx, $str );
        $this->print_colored( $str, $color );
    }

    protected function log( $str ){
        $this->print_colored( $str, 'white' );
    }

    protected function line( $char="\n", $length=1, $color='white' ){
        $line = join( '', array_fill( 0, $length, $char ));
        $this->print_colored( $line, $color );
    }

    private function print_colored( $string, $color = 'white' ){
        $color_seq = $this->_fg_colors[$color];
        printf( "\033[%sm%s\033[0m\n", $color_seq, $string );
    }

    private function mb_str_pad ($input, $pad_length, $pad_string=' ', $pad_style=STR_PAD_RIGHT, $encoding="UTF-8") {
        return str_pad($input,
            strlen($input)-mb_strlen($input,$encoding)
            + $pad_length, $pad_string, $pad_style);
    }


}
