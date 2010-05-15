<?php
class Nano_Element_Decorator{
    private $element;

    /**
     * This function is called by __toString and should be extended
     * for custom decorators
     *
     * @param Nano_Element $element The base element of this decorator
     * @return string $html
     */
    protected function render( Nano_Element $element ){
        return $this->renderElement( $element );
    }

    public final function __construct( Nano_Element $element ){
        $this->element = $element;
    }

    public final function __toString(){
        return $this->render( $this->element );
    }

    /**
     * Renders html code for a Nano_Element object
     *
     * @author Matthijs van Henten <matthijs@ischen.nl>
     * @copyright Copyright (c) 2009 Matthijs van Henten
     * @param Nano_Element $element A nano element
     * @return string $html
     */
    protected final function renderElement( $element ){
        $tagName    = $element->getType();
        $attributes = $this->renderAttributes( $element );

        foreach( $element->getChildren() as $child ){
            $element->addContent( rtrim((string) $child) );
        }


        if( null !== ( $content = $element->getContent() )
           || $element->vertile() ){
            if( null !== $content ){
                //$content = $content->map( 'rtrim', "\n")
                //         ->join("\n");

                $content = (array) $content;
                $with = array_fill( 0, count($content), "\n");
                $content = array_map( 'rtrim', $content,$with);
                $content = join( "\n", $content );
            }

            $html   = sprintf(
                "<%s%s>\n%s\n</%s>\n",
                $tagName,
                $attributes,
                $content,
                $tagName
            );
        }
        else{
            $html = sprintf( "<%s%s/>\n", $tagName, $attributes );
        }

        return $this->indent( $html );
    }

    protected function renderAttributes( Nano_Element $element ){
        $attributes = array_filter((array) $element->getAttributes());

        if( count( $attributes ) ){
            $values = array_map('htmlentities', $attributes);
            $keys   = array_map('htmlentities', array_keys($values));

            $attributes = array_map( 'sprintf', array_fill(0, count($keys), ' %s="%s"'), $keys, $values);
        }

        return join( '', $attributes );
    }

    protected function indent( $string ){
        $pieces = explode( "\n", $string );
        $collect = array();
        foreach( $pieces as $line ){
            $collect[] = "\t" . $line;
        }

        return join( "\n", $collect );
    }
}
