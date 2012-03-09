<?php
/**
 * library/Nano/Form/Element/Abstract.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


abstract class Nano_Form_Element_Abstract extends Nano_Element{
    protected $decorator = 'Nano_Form_Decorator_Input';

    protected $_label;
    protected $_type = 'input';
    protected $_validators = array();
    protected $_wrapper;
    protected $_required;
    protected $_prefix;
    protected $_suffix;
    protected $_errors  = array();


    /**
     *
     *
     * @param unknown $name
     * @param unknown $attributes
     */
    public function __construct( $name, $attributes ) {
        $defaults = array(
            'label'       => null,
            'required'    => false,
            'validators'  => array(),
            'validator'   => null,
            'prefix'      => null,
            'suffix'      => null,
            'elements'    => null,
            'wrapper'     => new Nano_Element( 'div', array('class' => 'formElementWrapper')),
        );


        $config = array_filter(array_merge( $defaults, array_intersect_key( $attributes, $defaults )));
        $attributes = array_diff_key( $attributes, $config );
        $attributes['name'] = $name;


        foreach ( $config as $key => $value ) {
            if ( ( $method = 'add' . ucfirst( $key ) ) && method_exists( $this, $method ) ) {
                $this->$method( $value );
            }
            else if ( ( $property = '_' . $key ) && property_exists( $this, $property ) ) {
                    $this->$property = $value;
                }
        }

        parent::__construct( $this->_type, $attributes );
    }


    /**
     * Add a form element. This method is different from add child,
     * since it takes in account that the element is one of the Form_
     * family, and may default to Input
     *
     *      as the name attribute. It therefore MUST be unique to this form.
     *
     * @param unknown $name       Name of the attribute. This MUST be set and will be used
     * @param unknown $attributes (optional) Will be passed immediately to the new element
     * @return Nano_Form_Element The newly created element
     */
    public function addElement( $name, $attributes = array() ) {
        if ( count($attributes) == 0 ) return true;

        $klass = 'Nano_Form_Element_Input';

        if ( $name instanceof Nano_Element ) {
            $this->addChild( $name );
            return $name;
        }

        if ( isset( $attributes['type'] ) ) {
            $k =  sprintf('Nano_Form_Element_%s', ucfirst($attributes['type']));
            if ( class_exists( $k ) ) {// input/type can be a separate class.
                $klass = $k;
            }
        }

        $element = new $klass( $name, $attributes );
        $this->addChild( $element );

        return $element;
    }


    /**
     * Add multipe elements in one go. $elements is expected to contain
     * a valid array of paramaters for addElement or it will throw an Exception
     *
     * @return Nano_Form $this
     * @param array   $elements Array with elements array($type=>'type', $name=>'nane',$value,$attr)
     */
    public function addElements( array $elements ) {
        foreach ( $elements as $name => $arguments ) {
            $this->addElement( $name, $arguments );
        }
    }


    /**
     * Return default arguments - use this to set up some defaults
     *
     * @return unknown
     */
    protected function getDefaultAttributes() {
        return array();
    }


    /**
     *
     *
     * @param unknown $validators
     * @return unknown
     */
    public function addValidators( $validators ) {
        foreach ( $validators as $validator ) {
            @list( $method, $options, $messages ) = $validator;

            $messages = is_array( $messages ) ? $messages : array();
            $this->addValidator( $method, $options, $messages );
        }
        return $this;
    }


    /**
     *
     *
     * @param unknown $method
     * @param unknown $options
     * @param array   $messages (optional)
     */
    public function addValidator( $method, $options, array $messages=array() ) {
        $class_name = sprintf('Nano_Form_Validator_%s', ucfirst($method));

        if ( ! class_exists( $class_name ) ) {
            array_unshift( $options, $method );
            $class_name = 'Nano_Form_Validator_Function';
        }

        $validator = new $class_name( $options, $messages );

        $this->_validators[] = $validator;
    }


    /**
     * Validates the form
     *
     * @param mixed   $post Post array. will be cast to array
     * @return bool $has_errors Wether the element has errors.
     */
    public function validate( $post ) {
        $childErrors = array();
        $this->_errors = array();

        foreach ( $this->getChildren() as $child ) {
            if ( $child instanceof Nano_Form_Element_Abstract ) {
                $childErrors = array_merge( $childErrors, $child->validate( $post ));
            }
        }

        if ( is_array( $this->_validators ) && count( $this->_validators ) > 0 ) {
            $key    = $this->getAttribute('name');
            $values = (array) $post;
            $value = key_exists( $key, $values ) ? $values[$key] : null;

            foreach ( $this->_validators as $validator ) {
                $return = $validator->validate( $value );

                if ( true !== $return ) {
                    $this->setError( $key, $return );
                }
            }
        }

        $this->_errors = array_merge( $childErrors, $this->getErrors() );

        if ( count( $this->_errors ) > 0 ) {
            $this->setAttribute( 'class', trim($this->getAttribute('class') . ' error'));
        }

        return $this->_errors;
    }


    /**
     * Check if form is valid. This function will return FALSE if the form
     * has not yet been validated!
     *
     * @return boolean $valid
     */
    public function isValid() {
        return !( (bool) count($this->_errors) );
    }


    /**
     *
     *
     * @param unknown $name
     * @param unknown $message
     */
    public function setError( $name, $message ) {
        if ( null == $this->_errors ) {
            $this->_errors = array();
        }

        if ( ! key_exists( $name, $this->_errors ) ) {
            $this->_errors[$name] = array();
        }

        $this->_errors[$name][] = $message;
    }


    /**
     *
     *
     * @return unknown
     */
    public function hasErrors() {
        return (bool) count( $this->_errors );
    }


    /**
     *
     *
     * @return unknown
     */
    public function getErrors() {
        if ( null == $this->_errors ) {
            $this->_errors = array();
        }

        return $this->_errors;
    }


    /**
     *
     *
     * @return unknown
     */
    private function getValidators() {
        if ( null == $this->validators ) {
            $this->validators = new Nano_Collection();
        }
        return $this->validators;
    }


    /**
     *
     *
     * @return unknown
     */
    public function getValue() {
        return $this->getAttribute( 'value' );
    }


    /**
     *
     *
     * @param unknown $value
     * @return unknown
     */
    public function setValue( $value ) {
        $this->setAttribute( 'value', $value );
        return $this;
    }


    /**
     *
     *
     * @param unknown $label
     * @return unknown
     */
    public function setLabel( $label ) {
        $this->_label = $label;
        return $this;
    }


    /**
     *
     *
     * @return unknown
     */
    public function getLabel() {
        return $this->_label;
    }


    /**
     *
     *
     * @param unknown $wrapper
     */
    public function setWrapper( $wrapper ) {
        if ( $wrapper instanceof Nano_Element ) {
            $this->_wrapper = $wrapper;
        }
        else if ( null !== $wrapper ) {
                $this->_wrapper = false;
            }
    }


    /**
     *
     *
     * @return unknown
     */
    public function getWrapper() {
        return $this->_wrapper;
    }


    /**
     *
     *
     * @param unknown $value
     */
    public function setPrefix( $value ) {
        $this->_prefix = $value;
    }


    /**
     *
     *
     * @return unknown
     */
    public function getPrefix() {
        return $this->_prefix;
    }


    /**
     *
     *
     * @param unknown $value
     */
    public function setSuffix( $value ) {
        $this->_suffix = $value;
    }


    /**
     *
     *
     * @return unknown
     */
    public function getSuffix() {
        return $this->_suffix;
    }


    /**
     *
     *
     * @param unknown $required (optional)
     */
    public function setRequired( $required = true ) {
        $this->_required = true;
    }


}
