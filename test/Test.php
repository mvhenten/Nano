<?php
require_once( dirname(dirname(__FILE__)) . '/library/Nano/Autoloader.php');
Nano_Autoloader::register();

class Test extends Nano_Test {
    public function test_init(){
        $this->block( 'overrides init ok');
    }

    public function test_first_test(){
        $this->ok( 'you should see a text wrapped in a box');
        $this->block( 'Weit hinten, hinter den Wortbergen, fern der Länder Vokalien und Konsonantien leben die Blindtexte. Abgeschieden wohnen Sie in Buchstabhausen an der Küste des Semantik, eines großen Sprachozeans. Ein kleines Bächlein namens Duden fließt durch ihren Ort und versorgt sie mit den nötigen Regelialien. Es ist ein paradiesmatisches Land, in dem einem gebratene Satzteile in den Mund fliegen. Nicht einmal von der allmächtigen Interpunktion werden die Blindtexte beherrscht - ein geradezu unorthographisches Leben.');
        $this->ok( 'you should see a red warning' );
        $this->warn( 'How are you today' );
        $this->ok( 'you should see a bright red error' );
        $this->error( 'This is not allright!');
        $this->info( 'this is ok');
        $this->dump( array('array', 'dump') );
    }
}

$test = new Test();
