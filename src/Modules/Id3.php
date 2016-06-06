<?php

namespace duncan3dc\MetaAudio\Modules;

use duncan3dc\MetaAudio\Exception;
use duncan3dc\Bom\Util as Bom;

/**
 * Handle ID3 tags.
 */
class Id3 extends AbstractModule
{
    const PREAMBLE = "ID3";

    /**
     * Get all the tags from the currently loaded file.
     *
     * @return array
     */
    protected function getTags()
    {
        $this->file->fseek(0, \SEEK_SET);

        $position = $this->file->getNextPosition(self::PREAMBLE);

        # If there is no ID3 tag then just return an empty array
        if ($position === false) {
            return [];
        }

        $this->file->fseek($position, \SEEK_CUR);

        $header = $this->parseHeader();

        $frames = $this->file->fread($header["size"]);

        $tags = [];
        while ($tag = $this->parseItem($frames)) {
            list($key, $value) = $tag;
            $tags[strtoupper($key)] = $value;
        }

        return $tags;
    }


    /**
     * Convert a synchsafe integer to a regular integer.
     *
     * In a synchsafe integer, the most significant bit of each byte is zero,
     * making seven bits out of eight available.
     * So a 32-bit synchsafe integer can only store 28 bits of information.
     *
     * @param string $string The synchsafe integer
     *
     * @param int
     */
    protected function fromSynchsafeInt($string)
    {
        $int = 0;
        for ($i = 1; $i <= 4; $i++) {
            $char = substr($string, $i - 1, 1);
            $byte = ord($char);
            $int += $byte * pow(2, (4 - $i) * 7);
        }

        return $int;
    }


    /**
     * Convert a regular integer to a synchsafe integer.
     *
     * @param int $int The integer
     *
     * @param string
     */
    protected function toSynchsafeInt($int)
    {
        $string = "";
        while ($int > 0) {
            $float = $int / 128;
            $int = floor($float);
            $char = chr(ceil(($float - $int) * 127));

            $string = $char . $string;
        }

        return str_pad($string, 4, "\x00", STR_PAD_LEFT);
    }


    /**
     * Parse the header from the file.
     *
     * @return array
     */
    protected function parseHeader()
    {
        $preamble = $this->file->fread(3);
        if ($preamble !== self::PREAMBLE) {
            throw new Exception("Invalid ID3 tag, expected [" . self::PREAMBLE . "], got [{$preamble}]");
        }

        $version = unpack("S", $this->file->fread(2))[1];
        $flags = unpack("C", $this->file->fread(1))[1];

        $header = [
            "version"   =>  $version,
            "flags"     =>  $flags,
            "size"      =>  $this->fromSynchsafeInt($this->file->fread(4)),
            "unsynch"   =>  (bool) ($flags & 0x80),
            "footer"    =>  (bool) ($flags & 0x10),
        ];

        # Skip the extended header
        if ($flags & 0x40) {
            $size = $this->fromSynchsafeInt($this->file->fread(4));
            $this->file->fread($size - 4);
            $header["size"] -= $size;
        }

        return $header;
    }


    /**
     * Get the next item tag from the file.
     *
     * @param string $frames The frames to parse the next one from
     *
     * @return array An array with 2 elements, the first is the item key, the second is the item's value
     */
    protected function parseItem(&$frames)
    {
        if (strlen($frames) < 1) {
            return;
        }

        $key = substr($frames, 0, 4);

        # Ensure a valid key was found
        if ($key < "AAAA" || $key > "ZZZZ") {
            return;
        }

        $size = $this->fromSynchsafeInt(substr($frames, 4, 4));

        $value = substr($frames, 11, $size - 1);

        $value = Bom::removeBom($value);

        $frames = substr($frames, 10 + $size);

        return [$key, $value];
    }


    /**
     * Write the specified tags to the currently loaded file.
     *
     * @param array The tags to write as key/value pairs
     *
     * @return void
     */
    protected function putTags(array $tags)
    {
        # Locate the existing id3 tags so we can strip them out
        $this->file->rewind();
        $start = $this->file->getNextPosition(self::PREAMBLE);
        if ($start !== false) {
            $this->file->fseek($start, \SEEK_CUR);
            $header = $this->parseHeader();
            $end = $this->file->ftell() + $header["size"];
        }

        # Get the contents of the file (without the id3 tags)
        $contents = "";
        $this->file->rewind();

        # If we found an id3 tag
        if ($start !== false) {
            # If the id3 tag isn't at the start of the file then get the data preceding it
            if ($start > 0) {
                $contents .= $this->file->fread($start);
            }
            # Position to the end of the id3 tag so we can start reading from there
            $this->file->fseek($end, \SEEK_SET);
        }

        # Read the rest of the file (following the id3 tag)
        $contents .= $this->file->readAll();

        $tags = $this->createTagData();

        $this->file->ftruncate(5);
        $this->file->rewind();
        $this->file->fwrite($tags);
        $this->file->fwrite($contents);
    }


    /**
     * Create the header for the file.
     *
     * @return string
     */
    protected function createTagData()
    {
        $header = self::PREAMBLE;

        # Version
        $header .= pack("S", 4);

        # Flags
        $header .= pack("C", 0);

        $tags = "";
        foreach ($this->tags as $key => $value) {
            $tags .= $key;
            $tags .= $this->toSynchsafeInt(strlen($value) + 1);
            $tags .= "   ";
            $tags .= $value;
        }

        $header .= $this->toSynchsafeInt(strlen($tags));

        return $header . $tags;
    }


    /**
     * Get the track title.
     *
     * @return string
     */
    public function getTitle()
    {
        return (string) $this->getTag("TIT2");
    }


    /**
     * Get the track number.
     *
     * @return int
     */
    public function getTrackNumber()
    {
        return (int) $this->getTag("TRCK");
    }


    /**
     * Get the artist name.
     *
     * @return string
     */
    public function getArtist()
    {
        return (string) $this->getTag("TPE1");
    }


    /**
     * Get the album name.
     *
     * @return string
     */
    public function getAlbum()
    {
        return (string) $this->getTag("TALB");
    }


    /**
     * Get the release year.
     *
     * @return int
     */
    public function getYear()
    {
        return (int) $this->getTag("TDRC");
    }


    /**
     * Set the track title.
     *
     * @param string $title The title name
     *
     * @return void
     */
    public function setTitle($title)
    {
        return $this->setTag("TIT2", $title);
    }


    /**
     * Set the track number.
     *
     * @param int $track The track number
     *
     * @return void
     */
    public function setTrackNumber($track)
    {
        return $this->setTag("TRCK", $track);
    }


    /**
     * Set the artist name.
     *
     * @param string $artist The artist name
     *
     * @return void
     */
    public function setArtist($artist)
    {
        return $this->setTag("TPE1", $artist);
    }


    /**
     * Set the album name.
     *
     * @param string $album The album name
     *
     * @return void
     */
    public function setAlbum($album)
    {
        return $this->setTag("TALB", $album);
    }


    /**
     * Set the release year.
     *
     * @param int $year The release year
     *
     * @return void
     */
    public function setYear($year)
    {
        return $this->setTag("TDRC", $year);
    }
}
