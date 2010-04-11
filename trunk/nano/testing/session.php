<?php
$session = new Nano_Session();
$session['TIME_' . time()] = 'Hello World';

foreach( $session as $key => $value ){
    echo "SESSION: $key => $value <br/>";
}

$session->biz = 'boo';

if( isset( $session->biz ) ){
    echo "biz was set";
}


unset( $session->biz );

echo "<hr/>";
if( count( $session ) > 10 ){
    foreach( $session as $key => $value ){
        unset( $session[$key] );
        echo "UNSET: $key => $value <br/>";
    }
}


