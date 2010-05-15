<?php
/**
 * Abstract base class for form decorators.
 */
class Nano_Form_Decorator_Abstract extends Nano_Element_Decorator{

    /**
     * This funciton is called by the __toString method or when a parent is
     * rendered. @todo the function is not very aptly named: it should be
     * 'pre-render' or 'preRenderElement' although it's disputable wheter renderElement
     * must be called from within this function or later.
     *
     * @return string $html HTML code for the element and it's children.
     */
    protected function render( Nano_Element $element ){
        $wrapper    = $element->getWrapper();
        $type       = $element->getType();
        $className  = $element->getAttribute('class');
        $label      = $element->getLabel();


        if( null == ( $id = $element->getAttribute('id') ) ){//generate unique id
            $count = $this->getElementCount();
            $element->setAttribute( 'id', sprintf('%s-element-%d', $element->getAttribute('type'), $count) );
        }

        if( empty( $className ) ){//generate auto classnames
            $className = sprintf('%s-%s', $type, $element->getAttribute('type'));
            $element->setAttribute( 'class', $className );
        }

        $content = $this->renderElement( $element );

        if( null !== $label ){//generate label around element
            $labelElement = new Nano_Element( 'label', array(
                'for'   => $element->getAttribute('id')
            ));

            $content = $this->renderElement( $labelElement->addContent( $label . $content ) );
        }

        if( $type !== 'hidden' ){
            $content = $this->renderElement( $wrapper->addContent( $content ) );
        }

        return (string) $content;
    }


    protected function getElementCount(){
        static $count = 0;
        return $count++;
    }
}
