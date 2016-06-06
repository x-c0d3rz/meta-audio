<?php

namespace duncan3dc\MetaAudioTests;

use duncan3dc\MetaAudio\File;
use duncan3dc\MetaAudio\Modules\Ape;
use duncan3dc\MetaAudio\Mp3;

class ConvertTest extends \PHPUnit_Framework_TestCase
{

    public function testRewrite()
    {
        $path = "/home/craig/music/from first to last/dead trees/01-Heresy....mp3";

        $tmp = tempnam("/tmp", "meta-audio-");
        copy($path, $tmp);

        $file = new File($tmp);

        $mp3 = new Mp3($file);
        $mp3->addModule(new Ape);

        $title = $mp3->getTitle();
        $track = $mp3->getTrackNumber();
        $artist = $mp3->getArtist();
        $album = $mp3->getAlbum();
        $year = $mp3->getYear();

        $mp3->setTitle($title);
        $mp3->setTrackNumber($track);
        $mp3->setArtist($artist);
        $mp3->setAlbum($album);
        $mp3->setYear($year);

        $this->assertSame($title, $mp3->getTitle());
        $this->assertSame($track, $mp3->getTrackNumber());
        $this->assertSame($artist, $mp3->getArtist());
        $this->assertSame($album, $mp3->getAlbum());
        $this->assertSame($year, $mp3->getYear());

        unset($mp3);

        $mp3 = new Mp3($file);
        $mp3->addModule(new Ape);


        $this->assertSame($title, $mp3->getTitle());
        $this->assertSame($track, $mp3->getTrackNumber());
        $this->assertSame($artist, $mp3->getArtist());
        $this->assertSame($album, $mp3->getAlbum());
        $this->assertSame($year, $mp3->getYear());

#        unlink($tmp);
    }
}
