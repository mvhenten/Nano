<?php
class Nano_Form_Element_Select extends Nano_Form_Element_Abstract{
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

        if( $options = $attributes['options'] ){
            $this->addOptions( $options );
        }

        unset( $attributes['options'] );
        parent::__construct( $name, $attributes );
    }

	public function addOptions( array $options ){
		foreach( $options as $value => $label ){
			$this->addOption( $value, $label );
		}
	}

	public function addOption( $value, $label ){
		$option = new Nano_Element('option', array('value'=>$value), $label);

		$this->addChild( $option );
		return $option;
	}
}
