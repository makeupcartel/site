<?php

namespace Unirgy\RapidFlow\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Unirgy\RapidFlow\Model\Feed;
use Unirgy\RapidFlow\Observer\AbstractObserver;

class AdminhtmlControllerActionPredispatch extends AbstractObserver implements ObserverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Feed
     */
    protected $_rapidFlowFeed;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Feed $rfFeed
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_rapidFlowFeed = $rfFeed;

    }

    /**
     * Check for extension update news
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->_scopeConfig->getValue('urapidflow/admin/notifications')) {
            try {
                $this->_rapidFlowFeed->checkUpdate();
            } catch (\Exception $e) {
                // silently ignore
            }
        }
    }
}
