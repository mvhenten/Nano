<?php
/**
 * @class Nano_Form_Validator_StringLength
 *
 * Abstract implementation of a form-validator class.
 * This class implements the 'not_empty' validator class as an example
 * and default behavior.
 */
class Nano_Form_Validator_StringLength extends Nano_Form_Validator_Abstract{
    protected $_messages = array(
        0   => 'String must be at least %d characters',
        1   => 'String cannot be more then %d characters'
    );
    /**
     * Function validate is called from the element.
     *
     * @param array $value Value to be validated by this class
     * @return mixed $success A boolean true, or a string containing the faillure
     */
    public function validate( $value ){
        $options = $this->getOptions();

        if( count($options) ){
            if( $options[0] > ($len = strlen($value)) ){
                return $this->getMessage(0, $options[0], $len, $value);
            }
            if( isset($options[1]) && $options[1] < ($len = strlen($value))){
                return $this->getMessage(1, $options[1], $len, $value);
            }
        }

        return true;
    }
}
