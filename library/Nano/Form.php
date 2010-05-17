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

	/**
	 * @return Nano_Form $form This form.
	 */
	public function addElement( $name, $attributes = array() ){
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

		foreach( $defaults as $key => $value ){
			unset( $attributes[$key] );
		}

        $attributes['name'] = $name;

        $type   = $this->getElementTagName( $config->type );
		$class  = 'Nano_Form_Element_' . ucfirst( $type );

        $attributes['type'] = $config->type;

        $element = new $class( $type, $attributes );

		//foreach( $config as $key => $value ){
		//	$element->$key = $value;
		//}

		//$element->prefix = $config->prefix;
		//$element->suffix = $config->suffix;
		//$element->wrapper = $config->wrapper;

        $element->setLabel( $config->label );
        $element->setRequired( $config->required );
		$element->setWrapper( $config->wrapper );
		$element->setPrefix( $config->prefix );
		$element->setSuffix( $config->suffix );


        foreach( $config->validators as $construct ){
            call_user_func_array( array( $element, 'addValidator'), $construct);
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
