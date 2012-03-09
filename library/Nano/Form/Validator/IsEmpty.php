<?php
/**
 *
 *
 * @class Nano_Form_Validator_IsEmpty
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


class Nano_Form_Validator_IsEmpty extends Nano_Form_Validator_Abstract{
    protected $_messages        = array('Value is required and cannot be left emtpy');

    /**
     *
     *
     * @param unknown $value
     * @return unknown
     */
    public function validate( $value ) {
        if ( strlen($value) == 0 ) {
            return $this->getMessage();
        }
        return true;
    }


}
