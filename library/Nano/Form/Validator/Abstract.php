<?php
/**
 * library/Nano/Form/Validator/Abstract.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


abstract class Nano_Form_Validator_Abstract {
    protected $_options         = array();
    protected $_messages        = array();

    /**
     *
     */
    public final function __construct( $options = array(), array $messages = array() ) {
        $this->_options = $options;

        foreach ( $messages as $code => $template ) {
            $this->setMessage( $template, $code );
        }
    }


    /**
     *
     */
    abstract public function validate( $value );

    /**
     *
     *
     * @param unknown $template
     * @param unknown $code     (optional)
     */
    public function setMessage( $template, $code = 0) {
        $this->_messages[$code] = $template;
    }


    /**
     *
     *
     * @param unknown $code (optional)
     * @param array   $args (optional)
     * @return unknown
     */
    protected function getMessage( $code = 0, array $args = array() ) {
        $template = isset( $this->_messages[$code] ) ? $this->_messages[$code] : $this->_messages[0];
        return vsprintf( $template, $args );
    }


    /**
     *
     *
     * @return unknown
     */
    protected function getOptions() {
        return $this->_options;
    }


}
