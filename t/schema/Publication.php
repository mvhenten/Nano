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
            'name'      => 'author_id',
            'extra'     => '',
            'required'  => true,
        ),

        'title' => array(
            'type'      => 'varchar',
            'length'    => 255,
            'default'   => '',
            'name'      => 'title',
            'extra'     => '',
            'required'  => true,
        ),
    );

    protected $_primary_key = array( 'id' );

    public function author(){
        return $this->has_one( 'Model_Author', array(
            'id' => 'author_id'
        ));
    }

    public function editors(){
        return $this->has_many_to_many( 'editors', 'Model_EditorPublication',
            array( 'publication_id' => 'id' ));
    }

    public function _filter_title( $value ){
        return $value . ', by ' . $this->author()->name;
    }
}
