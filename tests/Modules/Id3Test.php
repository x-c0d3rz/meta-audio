<?php

namespace duncan3dc\MetaAudioTests\Modules;

use duncan3dc\MetaAudio\Modules\Id3;

class Id3Test extends \PHPUnit_Framework_TestCase
{
    protected $id3;

    public function setUp()
    {
        $this->id3 = new Id3;
    }


    public function synchsafeProvider()
    {
        for ($i = 0; $i <= 9999; $i++) {
            yield [$i];
        }
    }
    /**
     * @dataProvider synchsafeProvider
     */
    public function testSynchsafeConversion($check)
    {
        $reflected = new \ReflectionClass($this->id3);

        $toSynchsafeInt = $reflected->getMethod("toSynchsafeInt");
        $toSynchsafeInt->setAccessible(true);
        $string = $toSynchsafeInt->invoke($this->id3, $check);

        $fromSynchsafeInt = $reflected->getMethod("fromSynchsafeInt");
        $fromSynchsafeInt->setAccessible(true);
        $result = $fromSynchsafeInt->invoke($this->id3, $string);

        $this->assertSame($check, $result);
    }
}
