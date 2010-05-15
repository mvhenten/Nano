<?php
class Nano_Form_Decorator_Textarea extends Nano_Form_Decorator{
    protected function render( Nano_Element $element ){
        $wrapper = $element->getWrapper();
        $value = $element->removeAttribute('value');

        $element->setVertile(true)
                ->setContent( $value );

        $attributes = (array) $element->getAttributes();
        $class = 'input-textarea';

        if( isset( $attributes['class'] ) ){
            $class = sprintf( '%s %s', $attributes['class'], $class );
        }

        $attributes['class'] = $class;
        $attributes['id'] = sprintf('textArea_%d', $this->getElementCount() );

        $attributes = array_map( 'htmlentities', array_filter($attributes) );

        foreach( $attributes as $key => $attr ){
            $attributes[$key] = sprintf('%s="%s"', $key, $attr );
        }

        $content = sprintf('<textarea %s>%s</textarea>', join(' ', $attributes), trim($value));


        if( null !== ( $label = $element->getLabel() ) ){
            $label = new Nano_Element(
                'label',
                array('for' => $element->getAttribute('id') ),
                $label
            );

            $content = $this->renderElement( $label->addContent( $content ) );
        }

        $content = $this->renderElement( $wrapper->addContent( $content ) );

        return $content;
    }
}
