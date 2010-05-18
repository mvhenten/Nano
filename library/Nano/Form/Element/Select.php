<?php
class Nano_Form_Element_Select extends Nano_Form_Element_Abstract{
    public function __construct( $type = null, array $attributes = null, $content = null ){
		unset( $attributes['type']);

		$attributes['class'] = 'select';

		parent::__construct( 'select', $attributes, $content );
		$this->setVertile(true);

		$this->addChild( new Nano_Element( 'option', array('selected'=>'selected', 'disabled'=>true), 'choose option'));
		if( null !== ( $options = $this->removeAttribute('options') ) ){
			$this->addOptions( $options );
		}
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
