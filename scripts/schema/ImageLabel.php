<?
class Model_Schema_ImageLabel extends Nano_Db_Schema {
    private $_tableName = 'image_label';

    private $_schema = array(
        'image_id' => array(
            'type'      => 'int',
            'length'    => 11,
            'default'   => '',
            'name'      => 'image_id',
            'extra'     => '',
            'required'  => true,
        ),

        'label_id' => array(
            'type'      => 'int',
            'length'    => 11,
            'default'   => '',
            'name'      => 'label_id',
            'extra'     => '',
            'required'  => true,
        ),

        'priority' => array(
            'type'      => 'int',
            'length'    => 10,
            'default'   => '',
            'name'      => 'priority',
            'extra'     => '',
            'required'  => true,
        )
    );

    private $_primary_key = array(
        array( 'image_id','label_id' )
    );



}