<?php
class Nano_Form_Decorator_Input extends Nano_Form_Decorator_Abstract{
    protected function render( Nano_Element $element ){
        if( $element->getAttribute('type') == 'hidden' ){
            $element->setWrapper( false ); // = false;//( false );
        }
        
        return parent::render( $element );
    }
}
