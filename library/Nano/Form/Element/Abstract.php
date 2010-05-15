<?php
abstract class Nano_Form_Element_Abstract extends Nano_Element{
    protected $label;
    protected $decorator = 'Nano_Form_Decorator_Input';
    protected $validators;
    protected $wrapper;

    /**
     * Return default arguments - use this to set up some defaults
     */
    protected function getDefaultAttributes(){
        return array();
    }

    public function addValidation( $function, $arguments = null, $message = null ){
        $this->getValidators()->push(
            new Nano_Form_Validation( $function, $arguments, $message )
        );
    }

    private function getValidators(){
        if( null == $this->validators ){
            $this->validators = new Nano_Collection();
        }
        return $this->validators;
    }

    public function getValue(){
        return $this->getAttribute( 'value' );
    }

    public function setValue( $value ){
        $this->setAttribute( 'value', $value );
        return $this;
    }

    public function setLabel( $label ){
        $this->label = $label;
        return $this;
    }

    public function getLabel(){
        return $this->label;
    }

    public function setWrapper( Nano_Element $wrapper ){
        $this->wrapper = $wrapper;
    }

    public function getWrapper(){
        if( null == $this->wrapper ){
            $this->wrapper = new Nano_Element( 'div', array(
                'class' => 'formElementWrapper'
            ) );
        }
        return $this->wrapper;
    }

}
