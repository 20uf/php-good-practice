<?php

/*
 * This file is part of the example good practice.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Criteria;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Class Request
 *
 * @author Michael COULLERET <michael@coulleret.pro>
 */
class Request 
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var ParameterBag
     */
    private $parameters;

    /**
     * Constructor class.
     *
     * @param $path
     * @param $parameters
     */
    public function __construct($path, $parameters = array())
    {
        $this->path = $path;
        $this->parameters = new ParameterBag($parameters);
    }

    /**
     * Get parameter bag
     *
     * @return ParameterBag
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set parameters bag
     *
     * @param ParameterBag $parameters
     *
     * @return self
     */
    public function setParameters(ParameterBag $parameters)
    {
        $this->parameters = $parameters;

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
     * Set path
     *
     * @param mixed $path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
