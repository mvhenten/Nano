<?php
class Nano_Form_Decorator_Form extends Nano_Form_Decorator_Abstract{
    protected function render( Nano_Element $element ){
        if( $element->hasErrors() ){
            $pfx = $element->getPrefix();
            $str = '';

            foreach( $element->getErrors() as $name => $errors ){
                $str .= sprintf('<dl><dt>%s</dt><dd><ul><li>%s</li></ul></dd></dt>',
                    $name, join('</li><li>', $errors ));
            }

            $str = '<div class="errors">' . $str . '</div>';

            $element->setPrefix( $pfx . $str );
        }

        return parent::render( $element );
    }
}
