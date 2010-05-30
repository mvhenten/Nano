<?php
abstract class Nano_Form_Element_Abstract extends Nano_Element{
    protected $decorator = 'Nano_Form_Decorator_Input';

    protected $_label;
    protected $_type = 'input';
    protected $_validators;
    protected $_wrapper;
    protected $_required;
    protected $_errors;
	protected $_prefix;
	protected $_suffix;


    public function __construct( $name, $attributes ){
        $defaults = array(
            'label'       => null,
            'required'    => false,
            'validators'  => array(),
            'validator'   => null,
			'prefix'	  => null,
			'suffix'	  => null,
            'elements'    => null,
            'wrapper'     => new Nano_Element( 'div', array('class' => 'formElementWrapper')),
        );


        $config = array_filter(array_merge( $defaults, array_intersect_key( $attributes, $defaults )));
        $attributes = array_diff_key( $attributes, $config );
        $attributes['name'] = $name;


        foreach( $config as $key => $value ){
            if( ( $method = 'add' . ucfirst( $key ) ) && method_exists( $this, $method ) ){
                $this->$method( $value );
            }
            else if( ( $property = '_' . $key ) && property_exists( $this, $property ) ){
                $this->$property = $value;
            }
        }

        parent::__construct( $this->_type, $attributes );
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
	 * Add a form element. This method is different from add child,
	 * since it takes in account that the element is one of the Form_
	 * family.
	 *
	 * @param $name Name of the attribute. This MUST be set and will be used
	 *      as the name attribute. It therefore MUST be unique to this form.
	 *
	 * @param $attributes Will be passed immediately to the new element
	 */
	public function addElement( $name, $attributes = array() ){
        if( count($attributes) == 0 ) return true;

        if( $attributes instanceof Nano_Element ){
            return $this->addChild( $attributes );
        }

        $klass =  sprintf('Nano_Form_Element_%s', ucfirst($attributes['type']));

        if( ! class_exists( $klass ) ){
            $klass = 'Nano_Form_Element_Input';
        }

        $element = new $klass( $name, $attributes );

        $this->addChild( $element );

        return $element;
	}

	/**
	 * Add multipe elements in one go. $elements is expected to contain
	 * a valid array of paramaters for addElement or it will throw an Exception
	 *
	 * @param array $elements Array with elements array($type=>'type', $name=>'nane',$value,$attr)
	 * @return Nano_Form $this
	 */
	public function addElements( array $elements ){
		foreach( $elements as $name => $arguments ){
            $this->addElement( $name, $arguments );
			//call_user_func( array( $this, 'addElement'), $name, $arguments );
		}
	}

    /**
     * Return default arguments - use this to set up some defaults
     */
    protected function getDefaultAttributes(){
        return array();
    }

    public function addValidators( $validators ){
        foreach( $validators as $validator ){
            @list( $method, $options, $breakOnFaillure ) = $validator;
            $this->addValidator( $method, $options, $breakOnFaillure );
        }
        return $this;
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

    public function setWrapper( $wrapper ){
		if( $wrapper instanceof Nano_Element ){
	        $this->_wrapper = $wrapper;
		}
		else if( null !== $wrapper ){
			$this->_wrapper = false;
		}
    }

    public function getWrapper(){
        return $this->_wrapper;
    }

	public function setPrefix( $value ){
		$this->_prefix = $value;
	}

	public function getPrefix(){
		return $this->_prefix;
	}

	public function setSuffix( $value ){
		$this->_suffix = $value;
	}

	public function getSuffix(){
		return $this->_suffix;
	}

    public function setRequired( $required = true ){
        $this->_required = true;
    }

}
