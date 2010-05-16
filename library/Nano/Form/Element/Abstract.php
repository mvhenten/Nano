<?php
abstract class Nano_Form_Element_Abstract extends Nano_Element{
    protected $decorator = 'Nano_Form_Decorator_Input';

    private $_label;
    private $_validators;
    private $_wrapper;
    private $_required;
    private $_errors;

    /**
     * Return default arguments - use this to set up some defaults
     */
    protected function getDefaultAttributes(){
        return array();
    }

    public function addValidator( $method, $options, $breakOnFaillure = false ){
        if( null === $this->_validators ){
            $this->_validators = array();
        }

        $className = sprintf('Nano_Form_Validator_%s', ucfirst($method));

        if( class_exists( $className ) ){
            $validator = new $className( $options, $breakOnFaillure );
        }
        else{
            //if( function_exists( $className ) ){
            $validator = new Nano_Form_Validator_Function( $method, $options, $breakOnFaillure );
        }

        $this->_validators[] = $validator;
    }

    public function validate( $post ){
        $childErrors = array();

        foreach( $this->getChildren() as $child ){
            if( $child instanceof Nano_Form_Element_Abstract ){
               $childErrors = array_merge( $childErrors, $child->validate( $post ));
            }
        }

        if( is_array( $this->_validators ) && count( $this->_validators ) > 0 ){
            $key    = $this->getAttribute('name');
            $values = (array) $post;
            $value = key_exists( $key, $values ) ? $values[$key] : null;

            foreach( $this->_validators as $validator ){
                $return = $validator->validate( $value );

                if( true !== $return ){
                    $this->setError( $key, $return );
                }
            }
        }

        $this->_errors = array_merge( $childErrors, $this->getErrors() );

        if( count( $this->_errors ) > 0 ){
            $this->setAttribute( 'class', trim($this->getAttribute('class') . ' error'));
        }

        return $this->_errors;
    }

    public function setError( $name, $message ){
        if( null == $this->_errors ){
            $this->_errors = array();
        }

        if( ! key_exists( $name, $this->_errors ) ){
            $this->_errors[$name] = array();
        }

        $this->_errors[$name][] = $message;
    }

    public function hasErrors(){
        return (bool) count( $this->_errors );
    }

    public function getErrors(){
        if( null == $this->_errors ){
            $this->_errors = array();
        }

        return $this->_errors;
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
        $this->_label = $label;
        return $this;
    }

    public function getLabel(){
        return $this->_label;
    }

    public function setWrapper( Nano_Element $wrapper ){
        $this->_wrapper = $wrapper;
    }

    public function getWrapper(){
        if( null == $this->_wrapper ){
            $this->_wrapper = new Nano_Element( 'div', array(
                'class' => 'formElementWrapper'
            ) );
        }
        return $this->_wrapper;
    }

    public function setRequired( $required = true ){
        $this->_required = true;
    }

}
