<?php

namespace duncan3dc\MetaAudioTests;

use duncan3dc\MetaAudio\File;
use duncan3dc\MetaAudio\Modules\Ape;
use duncan3dc\MetaAudio\Mp3;

class Mp3Test extends \PHPUnit_Framework_TestCase
{

    public function testRead()
    {
        $tmp = tempnam("/tmp", "meta-audio-");

        $file = new File($tmp);

        $mp3 = new Mp3($file);
        $mp3->addModule(new Ape);

        $artist = "Protest The Hero";
        $year = 2010;

        $mp3->setArtist($artist);
        $mp3->setYear($year);

        $this->assertSame($artist, $mp3->getArtist());
        $this->assertSame($year, $mp3->getYear());

        unset($mp3);

        $mp3 = new Mp3($file);
        $mp3->addModule(new Ape);

        $this->assertSame($artist, $mp3->getArtist());
        $this->assertSame($year, $mp3->getYear());

        unlink($tmp);
    }
}
