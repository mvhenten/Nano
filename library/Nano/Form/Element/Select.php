<?php
class Nano_Form_Element_Select extends Nano_Form_Element_Abstract{
    protected $decorator = 'Nano_Form_Decorator_Select';
    protected $_type      = 'select';

    /**
     * Create a new Nano_Fieldset
     *
     * @param array $attributes (optional) Key => Value pair of attributes
     * @return Nano_Form $form
     */
    public function __construct( $name, $attributes = array() ){
        $attributes = array_merge( array(
            'options'   => null
        ), $attributes );

        $options = $attributes['options'];

        unset( $attributes['options'] );
        parent::__construct( $name, $attributes );

        if( $options ){
            $this->addOptions( $options );
        }
    }

	public function addOptions( array $options ){
		foreach( $options as $value => $label ){
			$this->addOption( $value, $label );
		}
	}

	public function addOption( $value, $label, array $attributes = array() ){
        $attributes = array_merge( array(
            'value' => $value
        ), $attributes );


        if( $this->getValue() == $value ){
            $attributes['selected'] = 'selected';
        }

		$option = new Nano_Element('option', $attributes, $label);

		$this->addChild( $option );
		return $option;
	}
}
