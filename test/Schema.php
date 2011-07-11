<?php class Nano_Db_SchemaTest extends PHPUnit_Framework_TestCase{
    private $config;

    static function setUpBeforeClass(){
        require_once( dirname(dirname(__FILE__)) . '/library/Nano/Autoloader.php');
        Nano_Autoloader::register();
        require_once('schema/Author.php');
        require_once('schema/Publication.php');

        Nano_Db::setAdapter( array( 'dsn' => 'sqlite:test.db' ) );

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
            $author->put();
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
                $book = new Model_Publication();
                $book->put( array('author_id' => $author->id, 'title' => $title ));

                //printf( "%d: %s: %s\n", $book->id, $book->title, $author->name );
            }
        }
    }

    /**
     * @tepends testPutMore
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


    //public function testSearchWhere(){
    //    $model = new Model_Schema_Item();
    //
    //    foreach( $model->search( array('where' => array('slug' => 'schedule' )) ) as $item ){
    //        $this->assertEquals( 'schedule', $item->slug, 'Search returns expected result' );
    //    }
    //
    //    $ids = array();
    //    foreach( $model->search( array('where' => array('id' => array('<', 99 ))) ) as $item ){
    //        $this->assertLessThan( 99, $item->id, "ID is less then 99" );
    //        $ids[] = $item->id;
    //    }
    //
    //    foreach( $model->search( array('where' => array('id' => array('IN', $ids ))) ) as $item ){
    //        $this->assertTrue( in_array($item->id, $ids ), 'ID expected to be in array');
    //        $this->assertType( 'Model_Schema_Item', $item, 'ITEM is a Model_Schema_item' );
    //    }
    //
    //    $sth = $model->search( array('where' => array('id' => array('<', 99 ))) );
    //
    //    $this->assertType( 'PDOStatement', $sth );
    //
    //    printf("ROWS: %s\n", $sth->rowCount());
    //    printf("QUERY: %s\n", $sth->queryString);
    //
    //
    //    //$pager = new Nano_Db_Pager( $sth );
    //    //
    //    //print 'COUNT: ' . $pager->count();
    //    //
    //
    //
    //
    //}
}
