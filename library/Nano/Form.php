<?php
class Nano_Form extends Nano_Form_Abstract_Element{
    const DEFAULT_FORM_METHOD    = 'post';
    const DEFAULT_FORM_ENCODING  = 'multipart/form-data';

    protected $type = 'form';

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
        $config = array(
            'label'      => null,
            'type'       => null,
            'wrapper'   => null
        );

        $config = (object) array_merge( $config, $attributes );

        unset( $attributes['label'] );
        unset( $attributes['type'] );
        unset( $attributes['wrapper']);

        $attributes['name'] = $name;

        $type   = $this->getElementTagName( $config->type );
		$class  = 'Nano_Form_' . ucfirst( $type );

        // todo implement better templating.
        if( $class == 'Nano_Form_Input' ){
            $attributes['type'] = $config->type;
        }

        //@TODO maybe implement this without trying to load?
        if( ! class_exists( $class ) ){
            $class      = 'Nano_Form_Element';
            $attributes['type'] = $config->type;
		}

        $input = new $class( $type, $attributes );

        if( null !== $config->wrapper ){
            $input->setWrapper( $config->wrapper );
        }
        if( null !== $config->label ){
            $input->setLabel( $config->label );
        }
        if( null !== $config->wrapper &&
           ($config->wrapper instanceof Nano_Element) ){
            $input->setWrapper( $config->wrapper );
        }

		$this->addChild( $input );

        return $input;
	}

    private function getElementTagName( $type ){
        switch( $type ){
            case 'textarea':
                return 'textarea';
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

	public function validate( $values ){

	}

	public function addValidation( $name, $function ){
	}
}
