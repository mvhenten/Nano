<?php
class Nano_Form extends Nano_Form_Element_Abstract{
    const DEFAULT_FORM_METHOD    = 'post';
    const DEFAULT_FORM_ENCODING  = 'multipart/form-data';

    protected $decorator = 'Nano_Form_Decorator_Input';
    protected $_type      = 'form';

    /**
     * Create a new Nano_Form
     *
     * @param array $attributes (optional) Key => Value pair of attributes
     * @return Nano_Form $form
     */
    public function __construct( $id = null, $attributes = array() ){
        $attributes = array_merge( array(
            'id'        => $id,
            'method'    => self::DEFAULT_FORM_METHOD,
            'enctype'   => self::DEFAULT_FORM_ENCODING,
            'wrapper'   => false,
        ), $attributes );

        parent::__construct( null, $attributes );
    }
}
