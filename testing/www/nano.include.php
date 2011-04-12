<?php
ini_set('display_errors', "true");
ini_set('display_warnings', "true");
ini_set('upload_max_filesize', '16M');
ini_set('post_max_size', '16M');
define( "APPLICATION_ROOT", dirname(__FILE__) ); // the root of the application
define( "APPLICATION_PATH", dirname(dirname( APPLICATION_ROOT ))); //where the application is

require_once( APPLICATION_PATH . '/library/Nano/Autoloader.php');
Nano_Autoloader::register();
