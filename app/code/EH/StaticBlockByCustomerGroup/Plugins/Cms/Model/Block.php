<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2019 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_StaticBlockByCustomerGroup
 */

namespace EH\StaticBlockByCustomerGroup\Plugins\Cms\Model;

use Magento\Cms\Model\Block as CmsBlock;
use Magento\Cms\Model\BlockFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;

class Block
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Block factory
     *
     * @var BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * Construct
     *
     * @param StoreManagerInterface $storeManager
     * @param BlockFactory $blockFactory
     * @param CustomerSession $_customerSession
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        CustomerSession $_customerSession
    ) {
        $this->_storeManager = $storeManager;
        $this->_blockFactory = $blockFactory;
        $this->_customerSession = $_customerSession;
    }

    public function afterIsActive(CmsBlock $subject, $result)
    {
        if (!$result) {
            return $result;
        }
        $blockId = $subject->getId();
        if ($blockId) {
            $storeId = $this->_storeManager->getStore()->getId();
            /** @var CmsBlock $block */
            $block = $this->_blockFactory->create();
            $block->setStoreId($storeId)->load($blockId);
            if (!$this->_canDisplay($block)) {
                return false;
            }
        }
        return $result;
    }

    /**
     * Check if block can be displayed
     *
     * @param $block
     * @return bool
     */
    protected function _canDisplay($block)
    {
        if ($block->getCustomerGroupIds()) {
            $customerGroupIds = explode(',', $block->getCustomerGroupIds());
            if (!in_array($this->_getCurrentCustomerGroupId(), $customerGroupIds)) {
                return false;
            }
        }
        return true;
    }

    protected function _getCurrentCustomerGroupId()
    {
        return ($this->_customerSession->getCustomerGroupId() != 0) ?
            $this->_customerSession->getCustomerGroupId() : 'n';
    }
}