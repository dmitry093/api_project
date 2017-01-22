<?php

namespace AppBundle\Entity;

/**
 * FileMetaData
 */
class FileMetaData
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var int
     */
    private $bytes;

    /**
     * @var string
     */
    private $modified;

    /**
     * @var string
     */
    private $mimetype;


    /**
     * Set filename
     *
     * @param string $filename
     *
     * @return FileMetaData
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set bytes
     *
     * @param integer $bytes
     *
     * @return FileMetaData
     */
    public function setBytes($bytes)
    {
        $this->bytes = $bytes;

        return $this;
    }

    /**
     * Get bytes
     *
     * @return int
     */
    public function getBytes()
    {
        return $this->bytes;
    }

    /**
     * Set modified
     *
     * @param string $modified
     *
     * @return FileMetaData
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return string
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set mimetype
     *
     * @param string $mimetype
     *
     * @return FileMetaData
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;

        return $this;
    }

    /**
     * Get mimetype
     *
     * @return string
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }
}

