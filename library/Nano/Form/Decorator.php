<?php
class Nano_Form_Decorator extends Nano_Element_Decorator{

    protected function render( Nano_Element $element ){
        if( 'form' !== ( $type = $element->getType() ) ){
            $wrapper = $element->getWrapper();

            if( null == $element->getAttribute('id') ){
                //@TODO better solution please
                $element->setAttribute( 'id', sprintf('formElement_%d', $this->getElementCount()));
            }
            // or, code textArea class to prevent this?
            if( $type == 'textarea' ){
                $element->setVertile('true')
                ->setContent( $element->removeAttribute('value') );
            }
            else if( 'hidden' == $element->getAttribute('type') ){
                return $this->renderElement( $element );
            }

            $type = $element->getAttribute('type');

            if( ! empty( $type ) ){
                $element->setAttribute('class',
                    trim(sprintf(
                        '%s input%s',
                        $element->getAttribute('class'),
                        ucfirst($type)
                )));
            }

            $content = $this->renderElement( $element );

            if( null !== ( $label = $element->getLabel() ) ){
                $label = new Nano_Element(
                    'label',
                    array('for' => $element->getAttribute('id') ),
                    $label
                );

                $content = $this->renderElement( $label->addContent( $content ) );
            }

            $content = $this->renderElement( $wrapper->addContent( $content ) );
        }
        else{
            $content = $this->renderElement( $element );
        }

        return $content;
    }

    protected function getElementCount(){
        static $count = 0;
        return $count++;
    }

}
