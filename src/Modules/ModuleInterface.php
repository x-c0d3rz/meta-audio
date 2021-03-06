<?php

namespace duncan3dc\MetaAudio\Modules;

/**
 * Interface that all modules must implement to read tags.
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
}
