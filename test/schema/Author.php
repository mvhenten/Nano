<?
class Model_Author extends Nano_Db_Schema {
    protected $_tableName = 'author';

    protected $_schema = array(
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
            'name'      => 'group',
            'extra'     => '',
            'required'  => true,
        ),
    );

    protected $_primary_key = array( 'id' );

    public function books(){
        return $this->has_many( 'Model_Publication', array(
            'id' => 'author_id'
        ));
    }
}
