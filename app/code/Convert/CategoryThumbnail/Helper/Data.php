<?php

namespace Convert\CategoryThumbnail\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 *
 * @package Convert\CategoryThumbnail\Helper
 */
class Data extends AbstractHelper
{
	const ATTRIBUTE_NAME = "category_thumbnail";

	const ATTRIBUTE_NAME_MOBLE = "category_mobile_thumbnail";

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	protected $_storeManager;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
	protected $urlHelper;

    /**
     * @var \Magento\Framework\Registry
     */
	protected $_registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
	protected $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
	protected $customerFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
	protected $_categoryLoader;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryLoader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Catalog\Model\CategoryFactory $categoryLoader,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Registry $registry,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Url\Helper\Data $urlHelper
	)
	{
		parent::__construct($context);
		$this->_categoryLoader = $categoryLoader;
		$this->customerFactory = $customerFactory;
		$this->_storeManager = $storeManager;
		$this->_registry = $registry;
		$this->urlHelper = $urlHelper;
		$this->customerSession = $customerSession;
	}

    /**
     * @param $id
     * @return mixed
     */
	public function getLoadCategory($id)
	{
		return $this->_categoryLoader->create()->load($id);
	}

    /**
     * @param $email
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getCustomers($email)
    {
        $websiteId = $this->_storeManager->getStore()->getId();
        $customers = $this->customerFactory->create()->getCollection()
        ->addAttributeToSelect("*")
        ->addAttributeToFilter("website_id", array("eq" => $websiteId))
        ->addFieldToFilter("email", array("eq" => $email));

        if(count($customers->getData()) < 1){
           return 1;
        }else{
           return 2;
        }
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getMediaUrl()
	{
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getImageUrl(\Magento\Catalog\Model\Category $category)
	{
		$image = $category->getData(self::ATTRIBUTE_NAME);

		return $this->getUrl($image);
	}

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getImageMobileUrl(\Magento\Catalog\Model\Category $category)
	{
		$image = $category->getData(self::ATTRIBUTE_NAME_MOBLE);

		return $this->getUrl($image);
	}

    /**
     * @param $value
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getUrl($value)
	{
		$url = false;
		if ($value) {
			if (is_string($value)) {
				$url = $this->_storeManager->getStore()->getBaseUrl(
						\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
					) . 'catalog/category/' . $value;
			} else {
				throw new \Magento\Framework\Exception\LocalizedException(
					__('Something went wrong while getting the image url.')
				);
			}
		}

		return $url;
	}

    /**
     * @return bool
     */
	public function isLoggedIn()
	{
		return $this->customerSession->isLoggedIn();
	}

    /**
     * @param $path
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getBaseUrl($path)
	{
		return $this->_storeManager->getStore()->getBaseUrl(
				\Magento\Framework\UrlInterface::URL_TYPE_WEB
			) . $path;
	}

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getStoreId()
	{
		return $this->_storeManager->getStore()->getId();
	}
}
