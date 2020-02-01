<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Post\Rss;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Store\Model\Store as StoreModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Customer\Model\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Blog\Model\Rss\Post\RssItemProvider;
use Magento\Framework\View\Page\Config as PageConfig;

/**
 * Class Listing
 *
 * @package Aheadworks\Blog\Block\Post\Rss
 */
class Listing extends AbstractBlock implements DataProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RssItemProvider
     */
    private $rssItemProvider;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var UrlBuilderInterface
     */
    private $rssUrlBuilder;

    /**
     * @var PageConfig
     */
    private $pageConfig;

    /**
     * @param TemplateContext $context
     * @param HttpContext $httpContext
     * @param RssItemProvider $rssItemProvider
     * @param UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        HttpContext $httpContext,
        RssItemProvider $rssItemProvider,
        UrlBuilderInterface $rssUrlBuilder,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->rssItemProvider = $rssItemProvider;
        $this->httpContext = $httpContext;
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->pageConfig = $context->getPageConfig();
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->setCacheKey('rss_aw_blog_posts_' . $this->getStoreId() . '_' . $this->getCustomerGroupId());
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    public function getRssData()
    {
        $storeId = $this->getStoreId();
        $customerGroupId = $this->getCustomerGroupId();
        $storeModel = $this->storeManager->getStore($storeId);

        $title = __('Blog Posts from %1', $storeModel->getFrontendName())->render();
        $lang = $this->_scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeModel
        );

        $data = [
            'title' => $title,
            'description' => $title,
            'link' => $this->getLinkToFeeds(),
            'charset' => 'UTF-8',
            'language' => $lang,
            'entries' => $this->rssItemProvider->retrieveDataItems($storeId, $customerGroupId)
        ];

        if ($image = $this->getImage($storeModel)) {
            $data['image'] = $image;
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getCacheLifetime()
    {
        return 1;
    }

    /**
     * @inheritdoc
     */
    public function isAllowed()
    {
        return $this->_scopeConfig->isSetFlag(
            'rss/aw_blog_rss/blog_posts',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritdoc
     */
    public function getFeeds()
    {
        $data = [];
        if ($this->isAllowed()) {
            $link = $this->getLinkToFeeds();
            $data = ['label' => __('Blog Posts'), 'link' => $link];
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function isAuthRequired()
    {
        return false;
    }

    /**
     * Get link to feeds
     *
     * @return string
     */
    private function getLinkToFeeds()
    {
        return $this->rssUrlBuilder->getUrl([
            'type' => 'aw_blog_posts',
            'store_id' => $this->getStoreId(),
            'cid' => $this->getCustomerGroupId(),
        ]);
    }

    /**
     * Get customer group id
     *
     * @return int
     */
    private function getCustomerGroupId()
    {
        $customerGroupId = (int)$this->getRequest()->getParam('cid');
        if ($customerGroupId == null) {
            $customerGroupId = $this->httpContext->getValue(Context::CONTEXT_GROUP);
        }
        return $customerGroupId;
    }

    /**
     * Get store id
     *
     * @return int
     */
    private function getStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        if ($storeId == null) {
            try {
                $storeId = $this->storeManager->getStore()->getId();
            } catch (NoSuchEntityException $exception) {
                $storeId = StoreModel::DEFAULT_STORE_ID;
            }
        }
        return $storeId;
    }

    /**
     * Prepare image
     *
     * @param Store|StoreInterface $store
     * @return array|bool
     */
    private function getImage($store)
    {
        $image = false;
        if ($this->pageConfig->getFaviconFile()) {
            $image = [
                'uri' => $this->pageConfig->getFaviconFile(),
                'title' => $store->getFrontendName(),
                'link' => $store->getBaseUrl()
            ];
        }

        return $image;
    }
}
