<?php
class Nano_View_Helper_Style extends Nano_View_Helper{    
    private $_styles;

    public function Style(){
        return $this;
    }

    public function __toString(){
        $elements = array();

        foreach( $this->getStyles() as $style ){
            //var_dump( $style );
            list( $content, $attributes ) = $style;
            $tag = empty($content) ? 'link' : 'style';
            
            
            if( $tag == 'link' ){
                $content = null;
            }
            
            //$element->setVertile(false);   
            
            //$tag == 'link' ? $content = null : '';
            
            $element = new Nano_Element( $tag, $attributes, trim($content) );

            $elements[] = preg_replace( '/\s{2,}/', '', (string) $element );//$element;//$element->isVertile();
        }

        return join( "\n", $elements ) . "\n";
    }

    public function append( $src = null, $content = '', array $attributes = array() ){
        $this->addStyle( $src, $content, $attributes );
    }

    public function prepend( $src = null, $content = '', array $attributes = array() ){
        $this->addStyle( $src, $content, $attributes, true);
    }

    public function clear(){
        $this->_styles = array();
    }

    private function addStyle( $src, $content, $attributes, $prepend = false ){
        $styles = $this->getStyles();

        if( !empty($src) ){
            $attributes = array_merge(array(
                'href'   => $src,
                'media'  => 'all',
                'type'   => 'text/css',
                'rel'    => 'stylesheet'
            ), $attributes);
        }

        $style = array($content, $attributes);

        if( $prepend ){
            array_unshift( $styles, $style );
        }
        else{
            $styles[] = $style;
        }

        $this->_styles = $styles;
    }

    private function getStyles(){
        if( null == $this->_styles ){
            $this->_styles = array();
        }

        return $this->_styles;
    }
}
