<?php
class Nano_Form_Element_Fieldset extends Nano_Form{
    protected $decorator = 'Nano_Element_Decorator';
    protected $type      = 'fieldset';

    /**
     * Create a new Nano_Fieldset
     *
     * @param array $attributes (optional) Key => Value pair of attributes
     * @return Nano_Form $form
     */
    public function __construct( array $arguments = array() ){
        $this->setAttributes( $arguments );

		if( null !== ( $label = $this->removeAttribute('label') ) ){
			$this->addChild( new Nano_Element( 'legend', null, $label ) );
		}
		//ok this is just a plain hack to allow for a different tag: fieldsets
		// don't always behave like normal html block elements.
		if( null !== ( $type = $this->removeAttribute('type') ) ){
			$this->type = $type;
		}

        if( null !== ( $elements = $this->removeAttribute( 'elements' ) ) ){
            $this->addElements( $elements );
        }
    }


}
