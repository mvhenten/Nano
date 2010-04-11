<?php
/**
 * Test script for Nano_Form();
 */

$form = new Nano_Form();

foreach( range( 1, 5 ) as $value ){
    //$form->addElement( 'input', array('name' => 'input' . $value, 'type' => 'text' ) );
}

$config = array(
    'name'  => array(
        'type'  => 'text',
        'label' => 'Name'
    ),
    'password'  => array(
        'type'  => 'password',
        'label' => 'Password'
    ),
    'story' => array(
        'type'  => 'textarea',
        'value' => 'Type your story here'
    ),
    'save'  => array(
        'type'  => 'submit',
        'value' => 'save'
    )
);

foreach(  $config as $name => $value ){
    $form->addElement( $name, $value );
}


/*
$form->addElement( 'name', array(
    'type'  => 'text',
    'label' => 'This is a label',
    'value' => 'default'
));

//todo: implement submit buttons!
$form->addElement( 'apply', array('type' => 'button' ) );
*/
echo $form;



$e = new Nano_Element('ul', null, 'test data');
$e->setAttribute( 'class', 'test' );


foreach( range( 1, 3 ) as $value ){
    $c = new Nano_Element('li');

    $c->addContent( 'list item ' . $value );

  $e->addChild( $c );

   $d = new Nano_Element('div', null, 'TEST');
   $d->addContent('test__');
   $c->addChild( $d );
}

//$e->addContent("test");

echo $e;

?>
