<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Convert\Wishlist\Helper;

/**
 * Wishlist Data Helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_postDataHelper = $postDataHelper;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function getAddAllParams()
    {
        $url = $this->_storeManager->getStore()->getUrl('cwishlist/index/allwishlist');
        return $this->_postDataHelper->getPostData($url, []);
    }
}