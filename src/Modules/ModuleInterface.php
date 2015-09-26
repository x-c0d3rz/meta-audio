<?php

namespace duncan3dc\MetaAudio\Modules;

/**
 * Interface that all modules must implement to read/write tags.
 */
interface ModuleInterface
{

    /**
     * Get the track title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get the track number.
     *
     * @return int
     */
    public function getTrackNumber();

    /**
     * Get the artist name.
     *
     * @return string
     */
    public function getArtist();

    /**
     * Get the album name.
     *
     * @return string
     */
    public function getAlbum();

    /**
     * Get the release year.
     *
     * @return int
     */
    public function getYear();

    /**
     * Set the track title.
     *
     * @param string $title The title name
     *
     * @return void
     */
    public function setTitle($title);

    /**
     * Set the track number.
     *
     * @param int $track The track number
     *
     * @return void
     */
    public function setTrackNumber($track);

    /**
     * Set the artist name.
     *
     * @param string $artist The artist name
     *
     * @return void
     */
    public function setArtist($artist);

    /**
     * Set the album name.
     *
     * @param string $album The album name
     *
     * @return void
     */
    public function setAlbum($album);

    /**
     * Set the release year.
     *
     * @param int $year The release year
     *
     * @return void
     */
    public function setYear($year);
}
