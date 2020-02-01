<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Data;

use Aheadworks\Blog\Api\Data\TagCloudItemInterface;
use Aheadworks\Blog\Api\Data\TagCloudItemExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Tag cloud item data model
 * @codeCoverageIgnore
 */
class TagCloudItem extends AbstractExtensibleObject implements TagCloudItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return $this->_get(self::TAG);
    }

    /**
     * {@inheritdoc}
     */
    public function setTag($tag)
    {
        return $this->setData(self::TAG, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostCount()
    {
        return $this->_get(self::POST_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPostCount($postCount)
    {
        return $this->setData(self::POST_COUNT, $postCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(TagCloudItemExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
