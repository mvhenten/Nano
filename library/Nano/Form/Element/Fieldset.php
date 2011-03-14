<?php
class Nano_Form_Element_Fieldset extends Nano_Form_Element_Abstract{
    protected $_type      = 'fieldset';

    /**
     * Create a new Nano_Fieldset
     *
     * @param array $attributes (optional) Key => Value pair of attributes
     * @return Nano_Form $form
     */
    public function __construct( $name, $attributes = array() ){
        $attributes = array_merge( array(
            'id'        => $name,
            'wrapper'   => false,
            'label'     => null,
            'legend'    => null,
            'tagname'   => null
        ), $attributes );


        if( ($legend = $attributes['legend']) || ($legend = $attributes['label']) ){
            $this->addChild( new Nano_Element( 'legend', null, $legend ) );
            unset($attributes['label']);
        }

        if( ( $tagname = $attributes['tagname'] ) ){
            $this->_type = $tagname;
            unset( $attributes['tagname'] );
        }

        parent::__construct( null, $attributes );
        $this->setVertile();
    }


}
