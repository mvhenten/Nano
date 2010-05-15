<?php
class Nano_Form_Decorator_Textarea extends Nano_Form_Decorator_Abstract{
    protected function render( Nano_Element $element ){
        $wrapper    = $element->getWrapper();
        $type       = $element->removeAttribute('type');
        $className  = $element->getAttribute('class');
        $label      = $element->getLabel();
        $value      = $element->removeAttribute('value');

        if( null == ( $id = $element->getAttribute('id') ) ){//generate unique id
            $count = $this->getElementCount();
            $element->setAttribute( 'id', sprintf('textarea-%d', $count) );
        }

        if( empty( $className ) ){//generate auto classnames
            $className = sprintf('input-text input-%s', $type);
            $element->setAttribute( 'class', $className );
        }

        $attributes = $this->renderAttributes( $element );
        $content    = htmlentities( trim( $value ) );

        $content = sprintf('<textarea%s>%s</textarea>', $attributes, $content);

        if( null !== $label ){//generate label around element
            $labelElement = new Nano_Element( 'label', array(
                'for'   => $element->getAttribute('id')
            ));

            $content = $this->renderElement( $labelElement->addContent( $label . $content ) );
        }

        $content = $this->renderElement( $wrapper->addContent( $content ) );

        return (string) $content;


        //
        //
        //$attributes = (array) $element->getAttributes();
        //$class = 'input-textarea';
        //
        //if( isset( $attributes['class'] ) ){
        //    $class = sprintf( '%s %s', $attributes['class'], $class );
        //}
        //
        //$attributes['class'] = $class;
        //$attributes['id'] = sprintf('textArea_%d', $this->getElementCount() );
        //
        //$attributes = array_map( 'htmlentities', array_filter($attributes) );
        //
        //foreach( $attributes as $key => $attr ){
        //    $attributes[$key] = sprintf('%s="%s"', $key, $attr );
        //}
        //
        //$content = sprintf('<textarea %s>%s</textarea>', join(' ', $attributes), trim($value));
        //
        //
        //if( null !== ( $label = $element->getLabel() ) ){
        //    $label = new Nano_Element(
        //        'label',
        //        array('for' => $element->getAttribute('id') ),
        //        $label
        //    );
        //
        //    $content = $this->renderElement( $label->addContent( $content ) );
        //}
        //
        //$content = $this->renderElement( $wrapper->addContent( $content ) );
        //
        //return $content;
    }
}
