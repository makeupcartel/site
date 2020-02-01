<?php

namespace Unirgy\RapidFlow\Model;

use Magento\AdminNotification\Model\Feed as ModelFeed;
use Magento\AdminNotification\Model\InboxFactory;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

class Feed extends ModelFeed
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        Context $context,
        Registry $registry,
        ConfigInterface $backendConfig,
        InboxFactory $inboxFactory,
        CurlFactory $curlFactory,
        DeploymentConfig $deploymentConfig,
        ProductMetadataInterface $productMetadata,
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;

        parent::__construct($context, $registry, $backendConfig, $inboxFactory, $curlFactory, $deploymentConfig,
                            $productMetadata, $urlBuilder, $resource, $resourceCollection, $data);
    }

    const FEED_URL = 'download.unirgy.com/Unirgy_RapidFlow-notifications.feed';

    public function getFeedUrl()
    {
        if (is_null($this->_feedUrl)) {
            $this->_feedUrl = ($this->_scopeConfig->isSetFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://')
                . self::FEED_URL;
        }
        return $this->_feedUrl;
    }

    public function getLastUpdate()
    {
        return $this->_cacheManager->load('urapidflow_notifications_lastcheck');
    }

    public function setLastUpdate()
    {
        $this->_cacheManager->save(time(), 'urapidflow_notifications_lastcheck');
        return $this;
    }
}
