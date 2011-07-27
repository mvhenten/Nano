<?
class Model_EditorPublication extends Nano_Db_Schema {
    protected $_tableName = 'editor_publication';

    protected $_schema = array(
        'editor_id' => array(
            'type'      => 'int',
            'length'    => 11,
            'default'   => '',
            'name'      => 'editor_id',
            'extra'     => '',
            'required'  => true,
        ),

        'publication_id' => array(
            'type'      => 'int',
            'length'    => 11,
            'default'   => '',
            'name'      => 'publication_id',
            'extra'     => '',
            'required'  => true,
        ),
    );

    protected $_primary_key = array(
        array( 'editor_id','publication_id' )
    );

    public function editors(){
        return $this->belongs_to( 'Model_Editor', array( 'id' => 'editor_id' ));
    }
}
