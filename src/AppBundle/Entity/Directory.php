<?php

namespace AppBundle\Entity;

/**
 * Directory
 */
class Directory
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $files;


    /**
     * Set path
     *
     * @param string $path
     *
     * @return Directory
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set files
     *
     * @param array $files
     *
     * @return Directory
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }

    /**
     * Get files
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
}

