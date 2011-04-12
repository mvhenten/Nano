<?php
include('nano.include.php');
Nano_Db::setAdapter(array(
    "dsn" => "mysql:dbname=pico;host=127.0.0.1",
    "username" => 'pico',
    "password" => 'pico'
));



class Item extends Nano_Db_Model{
    const FETCH_TABLENAME   = 'item';
    const FETCH_PRIMARY_KEY = 'id';

}

class Document extends Nano_Db_Model{

}

class Document_Label extends Nano_Db_Model{

}


$i = new Item();
foreach($i->all() as $n){
    var_dump($n);
}
