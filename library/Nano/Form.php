<?php
/**
 * library/Nano/Form.php
 *
 * @author Matthijs van Henten <matthijs@ischen.nl>
 * @package Nano
 */


class Nano_Form extends Nano_Form_Element_Abstract{
    const DEFAULT_FORM_METHOD    = 'post';
    const DEFAULT_FORM_ENCODING  = 'multipart/form-data';

    protected $decorator = 'Nano_Form_Decorator_Form';
    protected $_type      = 'form';

    /**
     * Create a new Nano_Form
     *
     * @return Nano_Form $form
     * @param unknown $id         (optional)
     * @param array   $attributes (optional) Key => Value pair of attributes
     */
    public function __construct( $id = null, $attributes = array() ) {
        $attributes = array_merge( array(
                'id'        => $id,
                'method'    => self::DEFAULT_FORM_METHOD,
                'enctype'   => self::DEFAULT_FORM_ENCODING,
                'wrapper'   => false,
                'class'     => 'form',
            ), $attributes );

        parent::__construct( null, $attributes );
    }


    /**
     *
     *
     * @param unknown $name
     * @param unknown $attributes (optional)
     * @return unknown
     */
    public function getFieldset( $name, $attributes = array() ) {
        return new Nano_Form_Element_Fieldset( $name, $attributes );
    }


}
