<?php

namespace Convert\Catalog\Block;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Sitemap
 *
 * @package Convert\Catalog\Block
 */
class Sitemap extends \Mageplaza\Sitemap\Block\Sitemap
{
    /**
     * @var StoreManagerInterface
     */
	protected $_storeManager;

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getStoreId()
	{
        if ($this->_storeManager === null) {
            $this->_storeManager = ObjectManager::getInstance()
                ->get(StoreManagerInterface::class);
        }
        return $this->_storeManager->getStore()->getId();
	}

    /**
     * @return \Magento\Cms\Model\ResourceModel\Page\Collection|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getPageCollection()
	{
		return $this->pageCollection->addFieldToFilter('is_active', \Magento\Cms\Model\Page::STATUS_ENABLED)
			->addFieldToFilter('identifier', ['nin' => $this->getExcludedPages()])
			->addStoreFilter($this->getStoreId());
	}
}
