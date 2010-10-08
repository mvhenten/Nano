<?php
class Nano_Form_Decorator_Textarea extends Nano_Form_Decorator_Abstract{
    protected function render( Nano_Element $element ){
        $element->addContent( $element->getAttribute( 'value' ) );
        $element->removeAttribute( 'value' );
        $element->removeAttribute( 'type' );
        $element->setVertile(true);

        // return parent::render( $element );
        $str = parent::render( $element );
        
        //@todo this fixes something that shouldn'be needin fixin.
        // whitespace is added to content and this is bad.
        preg_match( '/^(.+<textarea.+?>)(.+?)(<\/textarea.+)/s', $str, $match );
        list( $none, $a, $b, $c ) = $match;        
        return join( "", array($a, trim(htmlentities($b)), $c));        
    }
}
