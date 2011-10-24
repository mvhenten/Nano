<?php
error_reporting(E_ALL | E_STRICT);

class Nano_Db_Schema_PagerTest extends PHPUnit_Framework_TestCase{
    private $config;

    static function setUpBeforeClass(){
        $path = dirname(dirname(__FILE__));
        require_once( dirname($path) . '/library/Nano/Autoloader.php');
        Nano_Autoloader::register();

        require_once( $path . '/schema/Author.php');

        Nano_Db::setAdapter( array( 'dsn' => 'sqlite::memory:' ) );

        $dbh = Nano_Db::getAdapter();

        $dbh->query('
            CREATE TABLE "author" (
            "id"  INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            "name"  TEXT NOT NULL
            );
        ');

        $writers = array_map('trim', explode(',', '
            Albert Camus, Marcel Proust, Franz Kafka,
            Antoine de Saint-Exupéry, André Malraux, Louis-Ferdinand Céline,
            John Steinbeck, Ernest Hemingway, Alain-Fournier,
            Boris Vian, Simone de Beauvoir, Samuel Beckett,
            Jean-Paul Sartre, Umberto Eco, Aleksandr Solzhenitsyn,
            Jacques Prévert, Guillaume Apollinaire, Hergé,
            Anne Frank, Claude Lévi-Strauss, Aldous Huxley,
            George Orwell, René Goscinny and Albert Uderzo,
            Eugène Ionesco, Sigmund Freud, Marguerite Yourcenar,
            Vladimir Nabokov, James Joyce, Dino Buzzati,
            André Gide, Jean Giono, Albert Cohen,
            Gabriel García Márquez, William Faulkner,
            François Mauriac, Raymond Queneau, Stefan Zweig,
            Margaret Mitchell, D. H. Lawrence, Thomas Mann,
            Françoise Sagan, Vercors
        '));


        foreach( $writers as $name ){
            $author = new Model_Author(array('name' => $name ));
            $author->store();
        }
    }

    /**
     */
    public function testCount(){
        $author = new Model_Author();

        $this->assertEquals( 42, $author->count() );
    }

    public function testPageConstruct(){
        $author = new Model_Author();
        $pager = new Nano_Db_Schema_Pager( $author, null );
        $this->assertType( 'Nano_Db_Schema_Pager', $pager );
    }

    public function testPageCount(){
        $author = new Model_Author();
        $pager = new Nano_Db_Schema_Pager( $author, null );

        $this->assertEquals( 42, $pager->total );
    }

    public function testDefaultPageSize(){
        $author = new Model_Author();
        $pager = new Nano_Db_Schema_Pager( $author, null );

        $this->assertEquals( Nano_Db_Schema_Mapper::FETCH_LIMIT, $pager->pageSize );
    }

    public function testSetPage(){
        $author = new Model_Author();
        $pager = new Nano_Db_Schema_Pager( $author, null, array('page_size' => 6 ) );

        $pager->setPage( $pager->lastPage );
        $this->assertEquals( 6, $pager->currentPageSize );
    }

    public function testGetPage(){
        $author = new Model_Author();
        $pager = new Nano_Db_Schema_Pager( $author, null, array('page_size' => 9 ) );


        $rows =  $pager->getPage($pager->lastPage);


        foreach( $rows as $i => $row ){
            $this->assertType( 'Nano_Db_Schema', $row );
        }

        $this->assertEquals( 5, $i );
    }

    public function testGetPageWhere(){
        $author = new Model_Author();
        $pager = new Nano_Db_Schema_Pager( $author, array( 'id' => array(1,2,3,4,5) ) );


        $this->assertEquals( 5, $pager->total );
        $this->assertEquals( 5, $pager->currentPageSize );

        $rows =  $pager->getPage();

        foreach( $rows as $i => $row ){
            $this->assertType( 'Nano_Db_Schema', $row );
        }

        $this->assertEquals( 4, $i );
    }
}
