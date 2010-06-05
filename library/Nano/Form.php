<?php
class Nano_Form extends Nano_Form_Element_Abstract{
    const DEFAULT_FORM_METHOD    = 'post';
    const DEFAULT_FORM_ENCODING  = 'multipart/form-data';

    protected $decorator = 'Nano_Form_Decorator_Form';
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

    /**
     * factory function... create form elements... not sure if I want this...
     */
    public function __call( $name, $arguments ){
        if( strpos( $name, 'get' ) == 0 ){
            $className = sprintf('Nano_Form_Element_%s', substr($name, 3 ));

            if( class_exists( $className ) ){
                list( $name, $attributes ) = $arguments;
                return new $className( $name, $attributes );
            }
        }
    }

    /**
     * Factory function: return a new fieldset. must be appended afterwards!
     *
     * @param string $name Name of the fieldset
     * @param array $attributes Array op optional attributes
     *
     * @return Nano_Form_Element_Fieldset
     */
    public function getFieldset( $name, $attributes = array() ){
        return new Nano_Form_Element_Fieldset( $name, $attributes );
    }
}
