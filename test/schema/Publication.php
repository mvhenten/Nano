<?
class Model_Publication extends Nano_Db_Schema {
    protected $_tableName = 'publication';

    protected $_schema = array(
        'id' => array(
            'type'      => 'int',
            'length'    => 10,
            'default'   => '',
            'name'      => 'id',
            'extra'     => 'auto_increment',
            'required'  => true,
        ),

        'author_id' => array(
            'type'      => 'int',
            'length'    => 10,
            'default'   => '',
            'name'      => 'item_id',
            'extra'     => '',
            'required'  => true,
        ),

        'name' => array(
            'type'      => 'varchar',
            'length'    => 255,
            'default'   => '',
            'name'      => 'group',
            'extra'     => '',
            'required'  => true,
        ),
    );

    protected $_primary_key = array(
        array( 'id' )
    );

    public function author(){
        return $this->has_one( 'Model_Author', array(
            'author_id' => 'id'
        ));
    }
}
