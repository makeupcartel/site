<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model;

use Magento\Framework\DataObject;

/**
 * Class Sitemap
 * @package Aheadworks\Blog\Model
 */
class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * {@inheritdoc}
     */
    protected function _initSitemapItems()
    {
        parent::_initSitemapItems();
        $this->_eventManager->dispatch('aw_sitemap_items_init', ['object' => $this]);
    }

    /**
     * Add sitemap items
     *
     * @param DataObject[] $items
     * @return $this
     */
    public function appendSitemapItems($items)
    {
        $this->_sitemapItems = array_merge($this->_sitemapItems, $items);
        return $this;
    }
}
