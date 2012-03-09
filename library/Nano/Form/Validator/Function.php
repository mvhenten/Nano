<?php
/**
 * library/Nano/Form/Validator/Function.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


class Nano_Form_Validator_Function extends Nano_Form_Validator_Abstract {

    /**
     *
     *
     * @param unknown $value
     * @return unknown
     */
    public function validate( $value ) {
        $options = $this->getOptions();

        $callback  = array_shift( $options );
        array_unshift( $options, $value );

        $success = call_user_func_array( $callback, $options );

        if ( ( (bool) $success == false ) || $success < 0  ) {
            return $this->getMessage(0, $options );
        }

        return true;
    }


}
