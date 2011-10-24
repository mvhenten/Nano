<?php
error_reporting(E_ALL | E_STRICT);

class Nano_Db_SchemaTest extends PHPUnit_Framework_TestCase{
    private $config;

    static function setUpBeforeClass(){
        require_once( dirname(dirname(__FILE__)) . '/library/Nano/Autoloader.php');
        Nano_Autoloader::register();
        require_once('schema/Author.php');
        require_once('schema/Publication.php');
        require_once('schema/Editor.php');
        require_once('schema/EditorPublication.php');

        Nano_Db::setAdapter( array( 'dsn' => 'sqlite::memory:' ) );

        $dbh = Nano_Db::getAdapter();

        $dbh->query('
            CREATE TABLE "author" (
            "id"  INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            "name"  TEXT NOT NULL
            );
        ');

        $dbh->query('
            CREATE TABLE "publication" (
            "id"  INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            "author_id"  INTEGER NOT NULL,
            "title"  TEXT NOT NULL
            );
        ');

        $dbh->query('
            CREATE TABLE "editor_publication" (
            "editor_id"  INTEGER NOT NULL,
            "publication_id"  INTEGER NOT NULL
            );
        ');

        $dbh->query('
            CREATE UNIQUE INDEX "editor_to_publication"
            ON "editor_publication" ("editor_id", "publication_id");
        ');

        $dbh->query('
            CREATE TABLE "editor" (
            "id"  INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            "name"  TEXT NOT NULL
            );
        ');

    }

    static function tearDownAfterClass(){
        unlink( 'test.db' );
    }

    public function testPut(){
        $names = array_map('trim', explode(',', 'Paul Auster, Tim Roth,
                            Haruki Murakami, Jonathan Safran Foer, Jonathan Franzen'));

        foreach( $names as $name ){
            $author = new Model_Author();
            $author->name = $name;
            $author->store();

        }

        return $names;
    }

    /**
     * @depends testPut
     */
    public function testSearch( $names ){
        $author = new Model_Author();

        foreach( $author->search() as $item ){
            $this->assertType( 'Model_Author', $item );
            $this->assertContains( $item->name, $names, 'ok' );
        }

        return $author->search();
    }


    /**
     * @depends testSearch
     */
    public function testPutMore( $authors ){
        $words = explode( ' ', 'time person year way day thing man world life hand');
        foreach( $authors as $author ){
            foreach( range(0,rand(0,10)) as $n ){
                shuffle( $words );
                $title = ucfirst( vsprintf('%s %s %s %s', array_slice($words, 0, 4)));
                $book = new Model_Publication(array('author_id' => $author->id, 'title' => $title ));
                $val = $book->store();
            }
        }

        return $authors;
    }

        /**
     * @depends testPutMore
     */
    public function testStoreFilterWorks( $authors ){
        foreach( $authors as $author ){
            $books = $author->books();
            foreach( $books as $book ){
                $this->assertType( 'Model_Publication', $book );
                $this->assertStringEndsWith( $author->name, $book->title );
            }
        }
    }

    /**
     * @depends testPutMore
     */
    public function testSearchMore(){
        $model = new Model_Publication();

        foreach( $model->search() as $item ){
            $this->assertType( 'Model_Publication', $item );
        }

        $collect = array();
        $items   = $model->search( array('where' => array('title' => array('LIKE', '%a%' ))) );
        foreach( $items as $item ){
            $collect[] = $model->id;
        }

        $this->assertGreaterThan( 1, $collect );

        $collect = array();
        $items   = $model->search( array('where' => array('author_id' => 1)) );
        foreach( $items as $item ){
            $collect[] = $model->id;
        }

        $this->assertGreaterThan( 1, $collect );
    }

    /**
     * @depends testPutMore
     */
    public function testHasOne(){
        $model  = new Model_Publication(1);
        $this->assertType( 'Model_Author', $model->author() );
    }

    /**
     * @depends testPutMore
     */
    public function testHasMany(){
        $model = new Model_Author(1);
        $books = $model->books();

        foreach( $model->books() as $publication ){
            $this->assertType( 'Model_Publication', $publication );
        }
    }

    /**
     * @depends testPutMore
     */
    public function testPutRelations(){
        $editors = explode(',', 'Joe Writer,Peter Publisher,Harry Howto,Eddie Editor');
        $model = new Model_Publication();

        $publications = $model->search();

        foreach( $editors as $index => $name ){
            $editor = new Model_Editor( array('name'=>$name) );
            $editor->id = $editor->store();

            $editors[$index] = $editor;


            foreach( $publications as $pub ){
                $editpub = new Model_EditorPublication();
                $editpub->editor_id = $editor->id;
                $editpub->publication_id = $pub->id;

                $editpub->store();
            }
        }

    }

    /**
     * @depends testPutRelations
     */
    public function testUpdateRelations(){
        $model = new Model_Editor();
        foreach( $model->search() as $editor ){
            $editor->name = 'updated ' . $editor->name;
            $editor->store(array( 'id' => $editor->id));
        }

        $model = new Model_Editor();
        foreach( $model->search() as $editor ){
            $this->assertEquals( 'updated', substr( $editor->name, 0, 7) );
        }

    }

    /**
     * @depends testPutRelations
     */
    public function testHasManyToMany(){
        $model = new Model_Publication;

        $editors = $model->editors();

        foreach( $editors as $editor ){
            $this->assertType( 'Model_Editor', $editor );
        }
    }

}
