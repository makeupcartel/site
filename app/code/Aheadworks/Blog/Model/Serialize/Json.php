<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Serialize;

/**
 * Class Json
 * @package Aheadworks\Blog\Model\Serialize
 */
class Json implements SerializeInterface
{
    /**
     * {@inheritdoc}
     */
    public function serialize($data)
    {
        $result = json_encode($data);
        if (false === $result) {
            throw new \InvalidArgumentException('Unable to serialize value.');
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($string)
    {
        $result = json_decode($string, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
        return $result;
    }
}
