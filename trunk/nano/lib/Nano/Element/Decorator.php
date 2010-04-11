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
        $attributes = array();

        foreach( $element->getChildren() as $child ){
            $element->addContent( rtrim((string) $child) );
        }

        foreach( $element->getAttributes() as $key => $value ){
            $attributes[] = " " . htmlentities($key) . '="' . htmlentities($value) . '"';
        }

        $attributes = rtrim(join('', $attributes));

        if( null !== ( $content = $element->getContent() )
           || $element->vertile() ){
            if( null !== $content ){
                $content = $content->map( 'rtrim', "\n")
                         ->join("\n");
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

    private function indent( $string ){
        $pieces = explode( "\n", $string );
        $collect = array();
        foreach( $pieces as $line ){
            $collect[] = "\t" . $line;
        }

        return join( "\n", $collect );
    }
}
