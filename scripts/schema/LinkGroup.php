<?
class Model_Schema_LinkGroup extends Nano_Db_Schema {
    private $_tableName = 'link_group';

    private $_schema = array(
        'id' => array(
            'type'      => 'int',
            'length'    => 10,
            'default'   => '',
            'name'      => 'id',
            'extra'     => 'auto_increment',
            'required'  => true,
        ),

        'name' => array(
            'type'      => 'varchar',
            'length'    => 255,
            'default'   => '',
            'name'      => 'name',
            'extra'     => '',
            'required'  => true,
        ),

        'description' => array(
            'type'      => 'varchar',
            'length'    => 1024,
            'default'   => '',
            'name'      => 'description',
            'extra'     => '',
            'required'  => true,
        )
    );

    private $_primary_key = array(
        array( 'id' )
    );



}