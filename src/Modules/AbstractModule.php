<?php

namespace duncan3dc\MetaAudio\Modules;

use duncan3dc\MetaAudio\File;

/**
 * Base class for modules to extend
 */
abstract class AbstractModule implements ModuleInterface
{
    /**
     * @var array|null $tags The parsed tags from the file.
     */
    protected $tags;

    /**
     * @var File $file The file to read.
     */
    protected $file;


    /**
     * Load the passed file.
     *
     * @param File $file The file to read
     *
     * @return static
     */
    public function open(File $file)
    {
        # If this file is already loaded then don't do anything
        if ($this->file) {
            $path1 = $this->file->getPath() . "/" . $this->file->getFilename();
            $path2 = $file->getPath() . "/" . $file->getFilename();
            if ($path1 === $path2) {
                return $this;
            }
        }

        $this->file = $file;
        $this->tags = null;

        return $this;
    }


    /**
     * Get all the tags from the currently loaded file.
     *
     * @return array
     */
    abstract protected function getTags();


    /**
     * Get a tag from the file.
     *
     * @param string $key The name of the tag to get
     *
     * @return mixed
     */
    protected function getTag($key)
    {
        if (!is_array($this->tags)) {
            $this->tags = $this->getTags();
        }

        if (!isset($this->tags[$key])) {
            return "";
        }

        return $this->tags[$key];
    }
}
