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
        if( null !== $value ){
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

    public function addChild( Nano_Element $element ){
        //$element->setParent( $this );

        $children = $this->getChildren();
        $children[] = $element;
        //
        //$this->getChildren()
        //    ->push( $element );
        //
        return $this;
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
