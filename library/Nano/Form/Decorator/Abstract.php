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

            $eType = ( $eType = $element->getAttribute('type') ) ? $eType : $type;

            $element->setAttribute( 'id', sprintf('%s-element-%d', $eType, $count) );
        }

        if( empty( $className ) ){//generate auto classnames
            $className = rtrim( sprintf('%s-%s', $type, $element->getAttribute('type')), '-');
            $element->setAttribute( 'class', $className );
        }

        $content = $this->renderElement( $element );

        if( null !== $label ){//generate label around element
            $labelElement = new Nano_Element( 'label', array(
                'for'   => $element->getAttribute('id'),
                'class' => "label-" . $element->getAttribute('type')
            ));
            
            $labelElement->setVertile();
            
            // checkbuttons and radios have a label on the right. saves gazillions of css.
            if( in_array( $element->getAttribute('type'), array( 'checkbox', 'radio' ) ) ){
                $content = $content . $this->renderElement( $labelElement->addContent( $label ) );                
            }
            else{
                $content = $this->renderElement( $labelElement->addContent( $label ) ) . $content;                
            }

        }

        if( $type !== 'hidden' && !empty($wrapper) ){
            $content = $this->renderElement( $wrapper->addContent( $content ) );
        }

        return sprintf( "%s%s%s", $element->getPrefix(), $content, $element->getSuffix() );
    }


    protected function getElementCount(){
        static $count = 0;
        return $count++;
    }
}
