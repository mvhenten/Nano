<?php
class Nano_Form_Decorator_Select extends Nano_Form_Decorator_Abstract{
    protected function render( Nano_Element $element ){
        if( null !== ( $label = $element->getLabel() ) && null == $element->getValue() ){
            $element->addOption( null, $label, array(
                'disabled' => true,
                'selected'   => true
            ) );
            $element->setLabel( false );
        }

        return parent::render( $element );
    }
}
