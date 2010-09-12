<?php
class Nano_Form_Decorator_Textarea extends Nano_Form_Decorator_Abstract{
    protected function render( Nano_Element $element ){
        $element->addContent( $element->getAttribute( 'value' ) );
        $element->removeAttribute( 'value' );
        $element->removeAttribute( 'type' );
        $element->setVertile(true);
        
        return parent::render( $element );
    }
}
