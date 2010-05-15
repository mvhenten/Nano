<?php
/**
 * Nano_Form_validation
 *
 * The purpose of this class is to allow form validation by writing snippets
 * of code for parsing trough create_function, or parsing by call_user_func
 * until a call to execute is made. The value passed to execute is then injected
 * as the first argument to the function.
 *
 * PHP version 5
 *
 * @author     Mathijs van Henten <matthijs@ischen.nl>
 * @copyright  2009 Matthijs van Henten
 * @license    http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class Nano_Form_Validation{
    private $method;
    private $arguments;
    private $message;

    /**
     * Create a new Form_Validation
     *
     * @param mixed $method Either input valid for call_user_func or create_function
     * @param array $arguments Key=>value pairs of additional arguments
     * @param string $message (optional) Error message to associate
     *
     * @return Nano_Form_Validation $validation
     */
    public function __construct( $method, $arguments = null, $message = null ){
        $this->method       = $method;
        $this->arguments    = $arguments;
        $this->message      = $message;
    }

    /**
     * Execute the validation function
     * -- compiles and runs the function passed as a constructor
     *
     * @param mixed $value Value to validate. This is injected as the first
     *                      argument of the validation function
     *
     * @return bool $valid
     */
    public function execute( $value ){
        $args = array_values( $this->getArguments() );
        // assert the order of parameters!
        if( count( $args ) > 0 ){
            array_unshift( $args, $value );
        }
        else{
            $args = (array) $value;
        }

        return (bool) call_user_func_array( $this->getMethod(), $args );
    }

    /**
     * Return method for validation
     * Evaluates trough is_callable, and calls createFunction otherwise.
     *
     * @return function $function Function parameter suitable for call_user_func
     */
    private function getMethod(){
        if( ! is_callable( $this->method ) ){
            $this->method = $this->createFunction();
        }

        return $this->method;
    }

    /**
     * Create an anonymous function. takes care of creating the variable names
     * as the first argument of create_function
     *
     * @return function $function Method for validation
     */
    private function createFunction(){
        $args = $this->getArguments();
        $key  = 'value';
        if( count( $args ) > 0 ){
            $keys = array_keys( $args );
            array_unshift( $keys, $key );
        }
        else{
            $keys = (array) $key;
        }

        foreach( $keys as $index => $key ){
            $keys[$index] = '$' . $key;
        }

        return create_function( join( ',', $keys ), $this->method );
    }

    /**
     * Return additional arguments passed to the constructor
     *
     * @return array $arguments
     */
    private function getArguments(){
        if( null == $this->arguments ){
            $this->arguments = array();
        }
        return $this->arguments;
    }
}
?>
