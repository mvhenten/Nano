<?php
/**
 * library/Nano/Element/Decorator.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


class Nano_Element_Decorator {
    private $element;

    /**
     * This function is called by __toString and should be extended
     * for custom decorators
     *
     * @param object  $element The base element of this decorator
     * @return string $html
     */
    protected function render( Nano_Element $element ) {
        return $this->renderElement( $element );
    }


    /**
     *
     */
    public final function __construct( Nano_Element $element ) {
        $this->element = $element;
    }


    /**
     *
     */
    public final function __toString() {
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
    protected final function renderElement( $element ) {
        $tagName    = $element->getType();
        $attributes = $this->renderAttributes( $element );

        foreach ( $element->getChildren() as $child ) {
            $element->addContent( rtrim((string) $child) );
        }

        $content = $element->getContent();

        if ( $element->vertile() || !empty($content) ) {
            if ( null !== $content ) {
                $content = (array) $content;
                $with = array_fill( 0, count($content), "\n");
                $content = array_map( 'rtrim', $content, $with);
                $content = join( "\n", $content );
            }

            $html   = sprintf( '<%s%s>', $tagName, $attributes );
            $html   = join( "\n", array_filter(array( $html, $content, "</$tagName>" )) );
        }
        else {
            $html = sprintf( "<%s%s/>\n", $tagName, $attributes );
        }

        if ( null !== $element->getParent() ) {
            return $this->indent( $html );
        }

        return $html;
    }


    /**
     *
     *
     * @param object  $element
     * @return unknown
     */
    protected function renderAttributes( Nano_Element $element ) {
        $attr = (array) $element->getAttributes();
        $attributes = array_filter( $attr, 'is_numeric' ); // catch strings contains 0
        $attributes = array_merge( $attributes, array_filter( $attr )); // catch the rest

        if ( count( $attributes ) ) {
            $values = array_map('htmlspecialchars', $attributes);
            $keys   = array_map('htmlspecialchars', array_keys($values));

            $attributes = array_map( 'sprintf', array_fill(0, count($keys), ' %s="%s"'), $keys, $values);
        }

        return join( '', $attributes );
    }


    /**
     *
     *
     * @param unknown $string
     * @return unknown
     */
    protected function indent( $string ) {
        $pieces = explode( "\n", $string );
        $collect = array();
        foreach ( $pieces as $line ) {
            $collect[] = "\t" . $line;
        }

        return join( "\n", $collect );
    }


}
