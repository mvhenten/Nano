<?php
/**
 * library/Nano/Form/Decorator/Form.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


class Nano_Form_Decorator_Form extends Nano_Form_Decorator_Abstract{

    /**
     *
     *
     * @param object  $element
     * @return unknown
     */
    protected function render( Nano_Element $element ) {
        if ( $element->hasErrors() ) {
            $pfx = $element->getPrefix();
            $str = '<h4>Your form submission contained some errors;
                   please correct those first</h4><ul>';

            foreach ( $element->getErrors() as $name => $errors ) {
                $str .= sprintf('<li>%s</li>', join('<br/>', $errors ));
            }

            $str = '<div class="form-errors">' . $str . '</ul></div>';

            $element->setPrefix( $pfx . $str );
        }

        return parent::render( $element );
    }


}
