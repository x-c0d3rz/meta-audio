<?php

namespace duncan3dc\MetaAudio;

/**
 * Read/write tags from an mp3 file.
 */
class Mp3
{
    use ModuleManager;

    /**
     * @var File $file The file handler.
     */
    protected $file;


    /**
     * Create a new instance from a local file.
     *
     * @param File $file The file to work with
     *
     * @return static
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }


    /**
     * Get a string from the active modules.
     *
     * Modules should be loaded in priority sequence as this method returns the first match.
     *
     * @param string $method The method name to call on the modules
     *
     * @return string
     */
    protected function getModuleString($method)
    {
        foreach ($this->modules as $module) {
            $module->open($this->file);
            $result = $module->$method();
            if (is_string($result) && strlen($result) > 0) {
                return $result;
            }
        }

        return "";
    }


    /**
     * Get an integer from the active modules.
     *
     * Modules should be loaded in priority sequence as this method returns the first match.
     *
     * @param string $method The method name to call on the modules
     *
     * @return int
     */
    protected function getModuleInt($method)
    {
        foreach ($this->modules as $module) {
            $module->open($this->file);
            $result = $module->$method();
            if (is_numeric($result) && $result > 0) {
                return (int) $result;
            }
        }

        return 0;
    }


    /**
     * Get the track title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getModuleString(__FUNCTION__);
    }


    /**
     * Get the track number.
     *
     * @return int
     */
    public function getTrackNumber()
    {
        return $this->getModuleInt(__FUNCTION__);
    }


    /**
     * Get the artist name.
     *
     * @return string
     */
    public function getArtist()
    {
        return $this->getModuleString(__FUNCTION__);
    }


    /**
     * Get the album name.
     *
     * @return string
     */
    public function getAlbum()
    {
        return $this->getModuleString(__FUNCTION__);
    }


    /**
     * Get the release year.
     *
     * @return int
     */
    public function getYear()
    {
        return $this->getModuleInt(__FUNCTION__);
    }


    /**
     * Set a value using all active modules.
     *
     * @param string $method The method name to call on the modules
     * @param mixed $value The value to pass to the module method
     *
     * @return static
     */
    protected function setModuleValue($method, $value)
    {
        foreach ($this->modules as $module) {
            $module->open($this->file);
            $module->$method($value);
        }

        return $this;
    }


    /**
     * Set the track title.
     *
     * @param string $title The title name
     *
     * @return static
     */
    public function setTitle($title)
    {
        return $this->setModuleValue(__FUNCTION__, (string) $title);
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
        return $this->setModuleValue(__FUNCTION__, (int) $track);
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
        return $this->setModuleValue(__FUNCTION__, (string) $artist);
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
        return $this->setModuleValue(__FUNCTION__, (string) $album);
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
        return $this->setModuleValue(__FUNCTION__, (int) $year);
    }
}
