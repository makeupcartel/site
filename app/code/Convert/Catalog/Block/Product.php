<?php

namespace Convert\Catalog\Block;

use Magento\Framework\Registry;

/**
 * Class Product
 *
 * @package Convert\Catalog\Block
 */
class Product extends \Magento\Framework\View\Element\Template 
{
    /**
     * @var Registry
     */
	protected $_coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	protected $_storeManager;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
     */
	protected $_blockColFactory;

    /**
     * Product constructor.
     *
     * @param Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockColFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
	public function __construct(
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Cms\Model\ResourceModel\Block\CollectionFactory $blockColFactory,
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = []
	)
	{
		$this->_coreRegistry = $coreRegistry;
		$this->_storeManager = $storeManager;
		$this->_blockColFactory = $blockColFactory;
		parent::__construct($context, $data);
	}

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getBaseURLMedia()
	{
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}

    /**
     * @return mixed
     */
	public function getProduct()
	{
		return $this->_coreRegistry->registry('current_product');
	}

    /**
     * @return mixed
     */
	public function cmsCollection()
	{
		return $this->_blockColFactory->create();
	}

    /**
     * @return mixed
     */
	public function getCurrentCategory()
	{        
		return $this->_coreRegistry->registry('current_category');
	}

}
