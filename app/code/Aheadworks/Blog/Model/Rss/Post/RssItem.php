<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Rss\Post;

use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class RssItem
 *
 * @package Aheadworks\Blog\Model\Rss\Post
 */
class RssItem extends AbstractExtensibleObject implements RssItemInterface
{
    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getLink()
    {
        return $this->_get(self::LINK);
    }

    /**
     * @inheritdoc
     */
    public function setLink($link)
    {
        return $this->setData(self::LINK, $link);
    }

    /**
     * @inheritdoc
     */
    public function getDateCreated()
    {
        return $this->_get(self::DATE_CREATED);
    }

    /**
     * @inheritdoc
     */
    public function setDateCreated($dateCreated)
    {
        return $this->setData(self::DATE_CREATED, $dateCreated);
    }
}
