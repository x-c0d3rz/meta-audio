<?php

namespace duncan3dc\MetaAudio;

/**
 * Read tags from an mp3 file.
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
}
