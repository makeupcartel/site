<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Observer;

use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Sitemap;
use Aheadworks\Blog\Model\Sitemap\ItemsProviderComposite;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddSitemapItemsObserver
 * @package Aheadworks\Blog\Observer
 */
class AddSitemapItemsObserver implements ObserverInterface
{
    /**
     * @var ItemsProviderComposite
     */
    private $itemsProvider;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ItemsProviderComposite $itemsProvider
     * @param Config $config
     */
    public function __construct(
        ItemsProviderComposite $itemsProvider,
        Config $config
    ) {
        $this->itemsProvider = $itemsProvider;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var Sitemap $sitemap */
        $sitemap = $event->getObject();
        $storeId = $sitemap->getStoreId();
        if ($this->config->isBlogEnabled($storeId)) {
            $sitemap->appendSitemapItems($this->itemsProvider->getItems($storeId));
        }
    }
}
