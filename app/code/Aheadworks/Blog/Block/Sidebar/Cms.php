<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Sidebar;

use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Source\Config\Cms\Block as BlockConfig;
use Aheadworks\Blog\Model\Url;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Sidebar Cms block
 * @package Aheadworks\Blog\Block\Sidebar
 */
class Cms extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    /**
     * @var Url
     */
    private $url;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var lockFactory
     */
    private $cmsBlockFactory;

    /**
     * @var FilterProvider
     */
    private $cmsFilterProvider;

    /**
     * @param Context $context
     * @param Url $url
     * @param Config $config
     * @param BlockFactory $cmsBlockFactory
     * @param FilterProvider $cmsFilterProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Url $url,
        Config $config,
        BlockFactory $cmsBlockFactory,
        FilterProvider $cmsFilterProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->url = $url;
        $this->config = $config;
        $this->cmsBlockFactory = $cmsBlockFactory;
        $this->cmsFilterProvider = $cmsFilterProvider;
    }

    /**
     * Retrieves CMS block
     *
     * @return bool|\Magento\Cms\Model\Block|null
     */
    public function getCmsBlock()
    {
        $cmsBlockId = $this->config->getSidebarCmsBlockId();
        if ($cmsBlockId && $cmsBlockId != BlockConfig::DONT_DISPLAY) {
            return $this->cmsBlockFactory->create()
                ->setStoreId($this->_storeManager->getStore()->getId())
                ->load($cmsBlockId);
        }
        return null;
    }

    /**
     * @param \Magento\Cms\Model\Block $cmsBlock
     * @return string
     */
    public function getCmsBlockHtml(\Magento\Cms\Model\Block $cmsBlock)
    {
        return $this->cmsFilterProvider->getBlockFilter()
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->filter($cmsBlock->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [\Magento\Cms\Model\Block::CACHE_TAG . '_' . $this->config->getSidebarCmsBlockId()];
    }
}
