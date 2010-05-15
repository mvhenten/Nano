<?php
class Nano_Form_Decorator_Checkbox extends Nano_Form_Decorator_Abstract{
    protected function render( Nano_Element $element ){
        $value = $element->getAttribute('value');
        $element->setType('input');
        $element->setAttribute( 'type', 'checkbox' );

        if( is_numeric( $value ) && $value > 0
           || is_string($value) && $value == 'true'
           || is_bool( $value ) && $value == true ){

            $element->setAttribute('checked', 'checked');
        }

        return parent::render( $element );
    }
}
