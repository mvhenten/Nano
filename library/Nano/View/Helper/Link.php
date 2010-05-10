<?php
/**
 * placeholder for link-helper
 * returns a <a href> thingky
 */
class Nano_View_Helper_Link{
    public function link( $target, $contents, $attributes = array() ){
        $attributes['href'] = $target;

        $element = new Nano_Element('a', $attributes, $contents );

        return (string) $element;
    }

}
