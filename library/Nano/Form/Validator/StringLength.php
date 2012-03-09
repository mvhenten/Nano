<?php
/**
 * library/Nano/Form/Validator/StringLength.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


class Nano_Form_Validator_StringLength extends Nano_Form_Validator_Abstract{
    protected $_messages = array(
        0   => 'String must be at least %d characters',
        1   => 'String cannot be more then %d characters'
    );

    /**
     *
     *
     * @param unknown $value
     * @return unknown
     */
    public function validate( $value ) {
        $options = $this->getOptions();

        @list( $min, $max )  = $this->getOptions();
        $len = strlen($value);

        if ( $len < $min ) {
            return $this->getMessage( 0, array( $max, $len, $value ) );
        }
        if ( $len > $max ) {
            return $this->getMessage( 1, array( $max, $len, $value ) );
        }

        return true;
    }


}
