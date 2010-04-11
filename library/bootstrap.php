<?php
error_reporting( E_ALL );
include( 'Nano/Autoloader.php');
Nano_Autoloader::register();

include '../testing/rotation.php';
exit;

if( isset( $_GET['table'] ) ){
    include( '../testing/table.php' );
}
else{
    include( '../testing/graph.php' );
}
