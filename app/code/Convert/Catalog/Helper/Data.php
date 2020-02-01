<?php

namespace Convert\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 *
 * @package Convert\Catalog\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
	protected $_session;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
	protected $_productLoader;

    /**
     * @var \Magento\Framework\Filesystem
     */
	protected $_filesystem;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
	protected $_imageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
	protected $_coreRegistry;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
	protected $_productImageHelper;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
	protected $_stockItemRepository;

    /**
     * @var \Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder
     */
	protected $_actionUrlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	protected $_storeManager;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
	protected $_filterProvider;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
	protected $jsonEncoder;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
	protected $_categoryCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
	protected $_scopeConfig;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $_imageBuilder;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Catalog\Model\ProductFactory $_productLoader
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder $actionUrlBuilder
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Catalog\Helper\Image $productImageHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Catalog\Model\CategoryFactory $categoryCollectionFactory
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Customer\Model\Session $session,
		\Magento\Catalog\Model\ProductFactory $_productLoader,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder $actionUrlBuilder,
		\Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
		\Magento\Framework\Image\AdapterFactory $imageFactory,
		\Magento\Catalog\Helper\Image $productImageHelper,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
		\Magento\Framework\Json\EncoderInterface $jsonEncoder,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\Magento\Catalog\Model\CategoryFactory $categoryCollectionFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\App\Http\Context $httpContext
	)
	{
		$this->_session = $session;
		$this->_actionUrlBuilder = $actionUrlBuilder;
		$this->_productLoader = $_productLoader;
		$this->_scopeConfig = $scopeConfig;
		$this->_coreRegistry = $coreRegistry;
		$this->_filesystem = $filesystem;
		$this->_directoryList = $directoryList;
		$this->_imageFactory = $imageFactory;
		$this->_productImageHelper = $productImageHelper;
		$this->_stockItemRepository = $stockItemRepository;
		$this->jsonEncoder = $jsonEncoder;
		$this->_storeManager = $storeManager;
		$this->_filterProvider = $filterProvider;
		$this->_categoryCollectionFactory = $categoryCollectionFactory;
		$this->_imageBuilder = $imageBuilder;
        $this->httpContext = $httpContext;
		parent::__construct($context);
	}

    /**
     * @return int
     */
	public function isCustomerLoggedIn()
	{
		if ($this->_session->isLoggedIn()) {
			return 1;
		}
		return 0;
	}

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->_session->isLoggedIn() || $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * @return array
     */
	public function getCategoryConvert()
	{
		$categoryId = $this->_scopeConfig->getValue('catalog/seo/category_id_parent', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$data = [];
		if ($categoryId && $categoryId != "" && $categoryId != null) {
			$category = $this->_categoryCollectionFactory->create()->load($categoryId);
			$childrenCategories = $category->getChildrenCategories();

            $currentCategory = $this->_coreRegistry->registry('current_category');
			$a = 0;
			foreach ($childrenCategories as $value) {
                $categoryColection = $this->_categoryCollectionFactory->create()->load($value->getId());
                if(!$categoryColection->getIsLayerCategory()){
                    $a++;
                    $data[$a]['name'] = $value->getName();
                    $data[$a]['url'] = $value->getUrlKey();
                    $data[$a]['url_parent'] = $category->getUrlKey();
                }
			}
			return $data;
		} else {
			return $data;
		}
	}

	/**
	 * @param $id
	 * @return \Magento\Catalog\Model\Product
	 */
	public function getLoadProduct($id)
	{
		return $this->_productLoader->create()->load($id);
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getUrlMedia()
	{
		return $this->_storeManager->getStore()->getBaseUrl();
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
     * @param $routpath
     * @param $websiteIds
     * @param $product_redirect_to
     * @return int|string
     */
	public function getBaseUrlMedia($routpath, $websiteIds, $product_redirect_to)
	{
		$_product = $this->_productLoader->create();
		$url = 1;
		$media = $this->_storeManager->getStores();

		if (count($websiteIds) == 2) {
			if ($websiteIds[1] == 2) {
				foreach ($media as $key => $value) {
					if ($value['code'] == 'poni') {
						$url = $this->_actionUrlBuilder->getUrl($routpath, $value['code'], $key);
					}
				}
			} else if ($websiteIds[1] == 3) {
				foreach ($media as $key => $value) {
					if ($value['code'] == 'esmi') {
						$url = $this->_actionUrlBuilder->getUrl($routpath, $value['code'], $key);
					}
				}
			}
		} else if (count($websiteIds) == 3) {
			$attr = $_product->getResource()->getAttribute("product_redirect_to");
			if ($attr->usesSource()) {
				$optionText = $attr->getSource()->getOptionText($product_redirect_to);
			}

			if ($optionText == 'esmi') {
				foreach ($media as $key => $value) {
					if ($value['code'] == 'esmi') {
						$url = $this->_actionUrlBuilder->getUrl($routpath, $value['code'], $key);
					}
				}
			} else if ($optionText == 'poni') {
				foreach ($media as $key => $value) {
					if ($value['code'] == 'poni') {
						$url = $this->_actionUrlBuilder->getUrl($routpath, $value['code'], $key);
					}
				}
			}
		}

		return $url;
	}

    /**
     * @param $imageFile
     * @param null $width
     * @param null $height
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function resizeImage($imageFile, $width = null, $height = null)
	{
		$absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product') . $imageFile;
		if (!file_exists($absolutePath)) return false;
		$imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/resized/' . $width . '/') . $imageFile;
		if (!file_exists($imageResized)) {
			$imageResize = $this->_imageFactory->create();
			$imageResize->open($absolutePath);
			$imageResize->constrainOnly(TRUE);
			$imageResize->keepTransparency(TRUE);
			$imageResize->keepFrame(FALSE);
			$imageResize->keepAspectRatio(TRUE);
			$imageResize->resize($width, $height);

			$destination = $imageResized;

			$imageResize->save($destination);
		}
		$resizedURL = $this->getMediaUrl() . 'catalog/product/resized/' . $width . '/' . $imageFile;

		return $resizedURL;
	}

    /**
     * @param $productId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getStockItem($productId)
	{
		return $this->_stockItemRepository->get($productId);
	}

    /**
     * @param $config
     * @return string
     */
	public function jsonEncoder($config)
	{
		return $this->jsonEncoder->encode($config);
	}

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getCurrentCurrencyCode()
	{
		return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
	}

    /**
     * @param $id
     * @return mixed
     */
	public function getEmailProductImage($id)
	{
		$product = $this->getLoadProduct($id);
		$productImage = $this->getImage($product, 'cart_page_product_thumbnail');
		$image_url = $productImage->getImageUrl();
        if ($this->isPubNeeded()) {
            return $image_url;
        }
        /* remove pub folder from the url */
		return str_replace('/pub/', '/', $image_url);
	}

    /**
     * @param $product
     * @param $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->_imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * @param $content
     * @return string
     * @throws \Exception
     */
	public function toHtml($content)
	{
		$html = '';
		if ($content) {
			$html = $this->_filterProvider->getBlockFilter()->filter($content);
		}
		return $html;
	}

    /**
     * @return bool
     */
	public function isPubNeeded()
    {
        $pub = $this->_directoryList->getUrlPath("pub");
        if ($pub == "pub") {
            return true;
        } else {
            return false;
        }
    }

}