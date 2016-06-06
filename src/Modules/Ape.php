<?php

namespace duncan3dc\MetaAudio\Modules;

use duncan3dc\MetaAudio\Exception;

/**
 * Handle APE tags.
 */
class Ape extends AbstractModule
{
    const PREAMBLE = "APETAGEX";

    /**
     * Get all the tags from the currently loaded file.
     *
     * @return array
     */
    protected function getTags()
    {
        $this->file->fseek(0, \SEEK_END);

        $position = $this->file->getPreviousPosition(self::PREAMBLE);

        # If there is no APE tag then just return an empty array
        if ($position === false) {
            return [];
        }

        $this->file->fseek($position, \SEEK_CUR);

        $header = $this->parseHeader();

        if ($header["footer"]) {
            $this->file->fseek($header["size"] * -1, \SEEK_CUR);
        }

        $tags = [];
        for ($i = 0; $i < $header["items"]; $i++) {
            list($key, $value) = $this->parseItem();
            $tags[strtolower($key)] = $value;
        }

        return $tags;
    }


    /**
     * Parse the header from the file.
     *
     * @return array
     */
    protected function parseHeader()
    {
        $preamble = $this->file->fread(8);
        if ($preamble !== self::PREAMBLE) {
            throw new Exception("Invalid Ape tag, expected [" . self::PREAMBLE . "], got [{$preamble}]");
        }

        $version = unpack("L", $this->file->fread(4))[1];
        $size = unpack("L", $this->file->fread(4))[1];
        $items = unpack("L", $this->file->fread(4))[1];
        $flags = unpack("L", $this->file->fread(4))[1];

        $header = [
            "version"   =>  $version,
            "size"      =>  $size,
            "items"     =>  $items,
            "flags"     =>  $flags,
            "footer"    =>  !($flags & 0x20),
        ];

        # Skip the empty space at the end of the header
        $this->file->fread(8);

        return $header;
    }


    /**
     * Get the next item tag from the file.
     *
     * @return array An array with 2 elements, the first is the item key, the second is the item's value
     */
    protected function parseItem()
    {
        $length = unpack("L", $this->file->fread(4))[1];

        $flags = unpack("L", $this->file->fread(4))[1];

        $key = "";
        while (!$this->file->eof()) {
            $char = $this->file->fread(1);
            if ($char === pack("c", 0x00)) {
                break;
            }
            $key .= $char;
        }

        if ($length > 0) {
            $value = $this->file->fread($length);
        } else {
            $value = "";
        }

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
        # Get the contents of the file (without the ape tags)
        $contents = "";
        $this->file->rewind();
        while (true) {
            $position = $this->file->getNextPosition(self::PREAMBLE);
            if ($position === false) {
                break;
            }

            # Get any content before the ape tag
            if ($position > 0) {
                $contents .= $this->file->fread($position);
            }

            $header = $this->parseHeader();
            $this->file->fseek($header["size"], \SEEK_CUR);
        }

        # Read the rest of the file (following the last ape tag)
        $contents .= $this->file->readAll();

        # Generate the new ape tags
        $tags = $this->createTagData();

        # Empty the file and position at the start so we can overwrite
        $this->file->ftruncate(0);
        $this->file->rewind();

        $this->file->fwrite($contents);
        $this->file->fwrite($tags);
    }


    /**
     * Create the header for the file.
     *
     * @return string
     */
    protected function createTagData()
    {
        $tags = "";
        foreach ($this->tags as $key => $value) {
            $tags .= pack("L", strlen($value));
            $tags .= pack("L", 0);
            $tags .= $key;
            $tags .= pack("c", 0x00);
            $tags .= $value;
        }

        $footer = self::PREAMBLE;

        # Version
        $footer .= pack("L", 2000);

        # Size (including the bytes for the footer)
        $footer .= pack("L", strlen($tags) + 32);

        # Number of tags
        $footer .= pack("L", count($this->tags));

        # Flags
        $footer .= pack("L", 0);

        $footer .= str_repeat(" ", 8);

        return $tags . $footer;
    }


    /**
     * Get the track title.
     *
     * @return string
     */
    public function getTitle()
    {
        return (string) $this->getTag("title");
    }


    /**
     * Get the track number.
     *
     * @return int
     */
    public function getTrackNumber()
    {
        return (int) $this->getTag("tracknumber");
    }


    /**
     * Get the artist name.
     *
     * @return string
     */
    public function getArtist()
    {
        return (string) $this->getTag("artist");
    }


    /**
     * Get the album name.
     *
     * @return string
     */
    public function getAlbum()
    {
        return (string) $this->getTag("album");
    }


    /**
     * Get the release year.
     *
     * @return int
     */
    public function getYear()
    {
        return (int) $this->getTag("year");
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
        return $this->setTag("title", $title);
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
        return $this->setTag("tracknumber", $track);
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
        return $this->setTag("artist", $artist);
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
        return $this->setTag("album", $album);
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
        return $this->setTag("year", $year);
    }
}
