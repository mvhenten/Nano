<?php
class Nano_Element{
    const DEFAULT_DECORATOR = 'Nano_Element_Decorator';
    const DEFAULT_TYPE      = 'div';

    protected $type;
    protected $decorator;

    private $parent;
    private $children;
    private $attributes;
    private $content;
    private $isVertile;

    public function __construct( $type = null, array $attributes = null, $content = null ){
        $this->setAttributes( (array) $attributes )
             ->setContent( $content )
             ->setType( $type );
    }

    public final function setVertile( $vertile = true ){
        $this->isVertile = $vertile;
        return $this;
    }

    public final function vertile(){
        return (bool) $this->isVertile;
    }

    public function setType( $type ){
        $this->type = $type;
        return $this;
    }

    public function getType(){
        if( null == $this->type ){
            $this->setType( self::DEFAULT_TYPE );
        }
        return $this->type;
    }

    public function __toString(){
        return (string) $this->getDecorator();
    }

    protected function getDecorator(){
        if( null == $this->decorator ){
            $this->setDecorator( self::DEFAULT_DECORATOR );
        }
        return new $this->decorator( $this );
    }

    protected function setDecorator( $decorator ){
        $this->decorator = $decorator;
    }

    public function getContent(){
        return $this->content;
    }

    public function setContent( $value = null ){
        if( $value instanceof Nano_Element ){
            $this->addChild( $value );
        }
        else if( null != $value ){
            $this->content = new Nano_Collection();
            $this->addContent( $value );
        }
        return $this;
    }

    public function addContent( $value ){
        if( null === $value ){
            return $this;
        }
        if( null === $this->getContent() ){
            $this->setContent( $value );
        }
        else{
            $content = $this->getContent();
            $content[] = $value;
            //$this->getContent()->push( $value );
        }

        return $this;
    }

    public function addChild( $args ){
        $args = func_get_args();
        $element = array_shift( $args );

        if( ! $element instanceof Nano_Element ){
            $attributes = array_shift( $args );
            $content    = array_shift( $args );

            $element = new Nano_Element( $element, $attributes, $content );
        }

        $children = $this->children();

        $children[] = $element;

        $this->children = $children;

        $element->setParent($this);

        return $this;
    }

    /**
     * Factory function: accepts an array as constructor options for Nano_Elements
     * $attributes may contain a special key 'content'
     *
     * @param array $children Array of type => attributes
     * @return $this liquid
     */
    public function addChildren( $children ){
        if( ! is_array( $children ) ){
            return;
        }

        foreach( $children as $type => $attributes ){
            $config = (object) array_merge(array(
                'children' => null,
                'content'  => null
            ), $attributes );

            unset( $attributes['children'] );
            unset( $attributes['content'] );

            $child = new Nano_Element( $type, $attributes, $config->content );
            $child->addChildren( $config->children );

            $this->addChild( $child );
        }

        return $this;
    }

    public function children( $selectors = array() ){
        if( null == $this->children ){
            $this->children = array();
        }

        if( empty($selectors) ){
            return $this->children;
        }

        $collect = array();
        $stack = (array) $this->children;

        while($stack){
            $child = array_pop($stack);
            $diff = array_diff($selectors, $child->attributes());

            if(empty($diff)){
                $collect[]=$child;
            }

            if( $child->hasChildren() ){
                $stack = array_merge($stack, $child->children());
            }
        }
        return $collect;
    }

    public function removeChildren( $selectors = array() ){
        if( null == $this->children ){
            $this->children = array();
        }

        if( empty($selectors) ){
            return $this->children;
        }

        $collect = array();
        $stack = $this->children;

        while($stack){
            $child = array_pop($stack);
            $diff = array_diff($selectors, $child->attributes());

            if(empty($diff)){
                $child = $child->getParent()->removeChild($child);
                $collect[] = $child;
            }
            else if( $child->hasChildren() ){
                $stack = array_merge($stack, $child->children());
            }
        }
        return $collect;
    }

    public function removeChild( $child ){
        $children = $this->children();
        $key = array_search( $child, $children );
        if( false !== $key ){
            unset($this->children[$key]);
        }
        return $child;
    }

    public function setParent( Nano_Element $element ){
        $this->parent = $element;
        return $this;
    }

    public function getParent(){
        return $this->parent;
    }

    public function getChildren(){
        return $this->children();
    }

    public function attributes(){
        if( null === $this->attributes ){
            $this->attributes = array();
        }
        return $this->attributes;
    }

    public function getAttributes(){
        return $this->attributes();
    }

    public function setAttributes( array $attributes ){
        foreach( $attributes as $key => $value ){
            $this->setAttribute( $key, $value );
        }
        return $this;
    }

    public function setAttribute( $name, $value ){
        $attributes = $this->getAttributes();
        $attributes[$name] = $value;

        $this->attributes = $attributes;
        return $this;
    }

    public function getAttribute( $name ){
        $attr = $this->attributes();
        if( isset($attr[$name])){
            return $attr[$name];
        }
    }

    public function removeAttribute( $name ){
        $attr = $this->attributes();
        unset($attr[$name]);
        $this->attributes = $attr;
    }

    public function hasChildren(){
        $children = $this->children();
        return !empty($children);
    }
}
