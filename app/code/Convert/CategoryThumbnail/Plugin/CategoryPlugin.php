<?php

namespace Convert\CategoryThumbnail\Plugin;

use Magento\Catalog\Model\Category as Subject;

/**
 * Class CategoryPlugin
 *
 * @package Convert\CategoryThumbnail\Plugin
 */
class CategoryPlugin
{
	/**
	 * Store manager
	 *
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $_storeManager;

	/**
	 * @var \Convert\CategoryThumbnail\Helper\Data
	 */
	protected $_helper;

	/**
	 * DataProviderPlugin constructor.
	 *
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Convert\CategoryThumbnail\Helper\Data $helper
	 */
	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Convert\CategoryThumbnail\Helper\Data $helper
	)
	{
		$this->_storeManager = $storeManager;
		$this->_helper = $helper;
	}

    /**
     * Around get data for preprocess image
     *
     * @param Subject $subject
     * @param \Closure $proceed
     * @param string $key
     * @param null $index
     * @return bool|mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function aroundGetData(Subject $subject, \Closure $proceed, $key = '', $index = null)
	{
		if ($key == \Convert\CategoryThumbnail\Helper\Data::ATTRIBUTE_NAME) {
			$result = $proceed($key, $index);
			if ($result) {
				return $this->_helper->getUrl($result);
			} else {
				return $result;
			}
		} else if ($key == \Convert\CategoryThumbnail\Helper\Data::ATTRIBUTE_NAME_MOBLE) {
			$result = $proceed($key, $index);
			if ($result) {
				return $this->_helper->getUrl($result);
			} else {
				return $result;
			}
		}
		return $proceed($key, $index);
	}
}
