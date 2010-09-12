<?php
/**
 *  Helper for <sript> tags
 * returns a <a href> thingky
 */
class Nano_View_Helper_Script{
    private $_scripts;

    public function script(){
    }

    public function __toString(){
        $elements = array();

        foreach( $this->getScripts() as $script ){
            //var_dump( $script );
            list( $content, $attributes ) = $script;

            $element = new Nano_Element( 'script', $attributes, trim($content) );
            $element->setVertile();

            $elements[] = preg_replace( '/\s{2,}/', '', (string) $element );//$element;//$element->isVertile();
        }

        return join( "\n", $elements ) . "\n";
    }

    public function append( $src = null, $content = '', array $attributes = array() ){
        $this->addScript( $src, $content, $attributes );
    }

    public function prepend( $src = null, $content = '', array $attributes = array() ){
        $this->addScript( $src, $content, $attributes, true);
    }

    public function clear(){
        $this->_scripts = array();
    }

    private function addScript( $src, $content, $attributes, $prepend = false ){
        $scripts = $this->getScripts();

        if( !empty($src) ){
            $attributes = array_merge(array(
                'src'   => $src
            ), $attributes);
        }

        $script = array($content, $attributes);

        if( $prepend ){
            array_unshift( $scripts, $script );
        }
        else{
            $scripts[] = $script;
        }

        $this->_scripts = $scripts;
    }

    private function getScripts(){
        if( null == $this->_scripts ){
            $this->_scripts = array();
        }

        return $this->_scripts;
    }
}
