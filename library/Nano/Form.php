<?php
class Nano_Form extends Nano_Form_Element_Abstract{
    const DEFAULT_FORM_METHOD    = 'post';
    const DEFAULT_FORM_ENCODING  = 'multipart/form-data';

    protected $decorator = 'Nano_Form_Decorator_Input';
    protected $type      = 'form';

    /**
     * Create a new Nano_Form
     *
     * @param array $attributes (optional) Key => Value pair of attributes
     * @return Nano_Form $form
     */
    public function __construct( array $arguments = array() ){
        $attributes = array_merge( $this->getDefaultAttributes(), $arguments );
        $this->setAttributes( $attributes );
    }

    /**
     * Return default arguments
     */
    protected function getDefaultAttributes(){
        return array(
            'method'    => self::DEFAULT_FORM_METHOD,
            'enctype'  => self::DEFAULT_FORM_ENCODING
        );
    }

    public function addFieldset( $name, $attributes ){
        $fielset = new Nano_Form_Element_Fieldset( $attributes );

        $this->addChild( $fieldset );

        return $fieldset;
    }

	/**
	 * @return Nano_Form $form This form.
	 */
	public function addElement( $name, $attributes = array() ){
        if( count($attributes) == 0 ) return true;
        if( $attributes instanceof Nano_Element ){
            $this->addChild( $attributes );
            return $attributes;
        }



        $defaults = array(
            'label'      => null,
            'type'       => null,
            'wrapper'   => null,
            'required'  => false,
            'validators'  => array(),
			'prefix'	=> null,
			'suffix'	=> null
        );

        $config = (object) array_merge( $defaults, $attributes );

        if( $config->type == 'fieldset' ){
            return $this->addFieldset( $name, $attributes );
        }

		foreach( $defaults as $key => $value ){
			unset( $attributes[$key] );
		}

        $attributes['name'] = $name;

        $type   = $this->getElementTagName( $config->type );
		$class  = 'Nano_Form_Element_' . ucfirst( $type );

        if( class_exists( $class ) ){
            $attributes['type'] = $config->type;

            $element = new $class( $type, $attributes );

            $element->setLabel( $config->label );
            $element->setRequired( $config->required );
            $element->setWrapper( $config->wrapper );
            $element->setPrefix( $config->prefix );
            $element->setSuffix( $config->suffix );


            foreach( $config->validators as $construct ){
                call_user_func_array( array( $element, 'addValidator'), $construct);
            }
        }


		$this->addChild( $element );

        return $element;
	}

    private function getElementTagName( $type ){
        switch( $type ){
            case 'textarea':
                return 'textarea';
            case 'checkbox':
                return 'checkbox';
			case 'select':
				return 'select';
            default:
                return 'input';
        }
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
			call_user_func( array( $this, 'addElement'), $name, $arguments );
		}
	}

}
