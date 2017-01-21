<?php
namespace AppBundle\Entity;


use AppBundle\Entity\FileMetaData;
class DataWrapper
{
    /**
     * My cool product
     *
     * @Type("AppBundle\Entity\Product")
     * @var Product
     */
    protected $FileMetaData;

    public function __construct()
    {
        $this->FileMetaData = new FileMetaData();
    }
}
