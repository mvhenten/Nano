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

    public function setContent( $value ){
        if( $value instanceof Nano_Element ){
            $this->addChild( $value );
        }
        else if( null !== $value ){
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
        $children = $this->getChildren();
        $args = func_get_args();

        $element = array_shift( $args );

        if( ! $element instanceof Nano_Element ){
            $attributes = array_shift( $args );
            $content    = array_shift( $args );

            $element = new Nano_Element( $element, $attributes, $content );
        }

        $children[] = $element;

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

    /**
     * Collect children of this element defined by $select
     * Note that this function returns any match!
     *
     * @param array $select Select may contain 'type', or any other attribute
     */
    public function findChildren( array $select = array() ){
        $children = $this->getChildren();

        if( count( $select ) == 0 ){
            return $children;
        }

        $config = (object) array_merge( array(
            'type'  => null,
            'id'    => null,
            'className' => null,
            'attributes' => null
        ), $select );

        unset( $select['type'] );
        unset( $select['id'] );
        unset( $select['id'] );

        $config->attributes = $select;
        $collect  = array();

        foreach( $children as $child ){
            if( ( $config->type && $child->getType() == $config->type )
               || ($config->id && $child->getAttribute('id') == $config->id)
               || ($config->className && $child->getAttribute('class') == $config->clasName )
            ){
                $collect[] = $child;
            }
            else if( count($config->attributes) > 0 ){
                foreach( $config->attributes as $key => $value ){
                    if( $child->getAttribute( $key ) == $value ){
                        $collect[] = $child;
                        break;
                    }
                }
            }
        }

        return $collect;
    }

    public function recursivelyFindChildren( array $select = array() ){
        $collect = $this->findChildren( $select );

        foreach( $this->getChildren() as $child ){
            array_merge( $collect, $child->recursivelyFindChildren($select));
        }

        return $collect;
    }

    public function setParent( Nano_Element $element ){
        $this->parent = $element;
        return $this;
    }

    public function getParent(){
        return $this->parent;
    }

    public function getChildren(){
        if( null === $this->children ){
            $this->children = new Nano_Collection();
        }

        return $this->children;
    }

    public function getAttributes(){
        if( null === $this->attributes ){
            $this->attributes = new Nano_Collection();
        }
        return $this->attributes;
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

        return $this;
    }

    public function getAttribute( $name ){
        return $this->getAttributes()->$name;
    }

    public function removeAttribute( $name ){
        $attr = $this->getAttributes();
        $value = $attr->$name;
        $attr->$name = null;
        return $value;
    }

    public function hasChildren(){
        return true;
        if( count( $this->getChildren() ) > 0 ){
            return true;
        }
        return false;
    }
}
