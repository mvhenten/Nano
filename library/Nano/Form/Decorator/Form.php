<?php
class Nano_Form_Decorator_Form extends Nano_From_Decorator_Abstract{

    protected function render( Nano_Element $element ){
        return $this->renderElement( $element );
    }
}
