<?php
/**
 * @class Nano_Form_Validator_Abstract
 *
 * Abstract implementation of a form-validator class.
 * This class implements the 'not_empty' validator class as an example
 * and default behavior.
 */
class Nano_Form_Validator_Abstract{
    protected $_breakOnFaillure = false;
    protected $_options         = array();
    protected $_messages        = array('Value is required and cannot be left emtpy');

    /**
     * Default class constructor. no need to change this.
     * Optionally 'messages' may be set to provide custom error messages
     *
     * @param array $options Options in the right order for this validator.
     * @param bool breakOnFaillure Sets internal value for later retrieval
     */
    public final function __construct( $options = array(), $breakOnFaillure = false, $messages = null ){
        $this->_breakOnFaillure = $breakOnFaillure;
        $this->_options = $options;

        if( null !== $messages ){
            $messages = (array) $messages;

            foreach( $messages as $code => $template ){
                $this->setMessage( $template, $code );
            }
        }
    }

    /**
     * Function validate is called from the element.
     *
     * @param array $value Value to be validated by this class
     * @return mixed $success A boolean true, or a string containing the faillure
     */
    public function validate( $value ){
        if( strlen($value) == 0 ){
            return $this->getMessage();
        }
        return true;
    }

    /**
     * Provide custom error message
     *
     * @param string $template A String explaining why validation failed.
     * @param int $code (optional) Status code this template describes.
     */
    public function setMessage( $template, $code = 0){
        $this->$messages[$code] = $template;
    }

    /**
     * Returns internal breakOnFaillure
     */
    public function breakOnFaillure(){
        return (bool) $this->_breakOnFaillure;
    }

    /**
     * Retrieve a status bound message or the default one (0)
     * sprintf is used to fill in values for options if needed.
     *
     * @param int $code (optional) status code to determine wich message to retrieve
     * @return string $message Message formatted with values from the options.
     */
    protected function getMessage( $code = 0, $args ){
        $args = func_get_args();

        $code = array_shift( $args );

        $template = isset( $this->_messages[$code] ) ? $this->_messages[$code] : $this->_messages[0];

        return vsprintf( $template, $args );
    }

    /**
     * Fetch options passed in the constructor
     */
    protected function getOptions(){
        if( $this->_options == null ){
            $this->_options = array();
        }

        return $this->_options;
    }
}
