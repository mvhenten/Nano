<?php
function E( $tagName = 'div', $attributes = null ){
    return new Nano_Element( $tagName, $attributes );
}


$e = E()->setContent('test')->setAttribute('foo', 'test')->setAttributes(array('id'=>'biz','class'=>'test'));

echo $e;

$e = E('ul');

foreach( array('one','two','three') as $value ){
    $e->addElement( 'li' )->setContent( $value );
}

echo $e;


echo E('br');


$form = E('form', array('encoding' => 'utf8', 'content-type' => 'application/octet-stream'));

foreach( array( 'text' => 'hello', 'checkbox' => 'test', 'submit' => 'save' ) as $type => $value ){
    $label = $form->addElement('label')
        ->setContent( $value );
    $label->addElement( 'input', array( 'type' => $type, 'value' => $value ) );
    $label->addElement( 'div' )->addContent('this is a span');


}

echo $form;

$e = E('div');
$e->addContent('test')
    ->addElement()
    ->addContent('nest')
    ->addElement()
    ->addContent( 'nest2' );

echo $e;
