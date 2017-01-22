<?php

namespace AppBundle\Entity;

/**
 * ApiResponse
 */
class ApiResponse
{
    /**
     * @var int
     */
    private $code;

    /**
     * @var array
     */
    private $parameters;


    /**
     * Set code
     *
     * @param integer $code
     *
     * @return ApiResponse
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set parameters
     *
     * @param array $parameters
     *
     * @return ApiResponse
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}

