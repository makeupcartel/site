<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Indexer\MultiThread;

/**
 * Class PostDimension
 *
 * @package Aheadworks\Blog\Model\Indexer\MultiThread
 */
class PostDimension
{
    /**
     * Name for post dimension for multidimensional indexer
     * _pp means post part
     */
    const DIMENSION_NAME = '_pp';

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $value;

    /**
     * @param string $name
     * @param array $value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Get dimension name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get value
     *
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }
}
