<?php
/**
 * Created by pp
 * @project magento2
 */

namespace Unirgy\RapidFlow\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product as RfProduct;

/**
 * Class Url
 * Helper class to generate product URL rewrites
 * @see \Magento\CatalogUrlRewrite\Observer\ProductProcessUrlRewriteSavingObserver
 * @package Unirgy\RapidFlow\Helper
 */
class Url extends AbstractHelper
{
    /**
     * @var array
     */
    protected $vitalForGenerationFields = [
        'sku',
        'url_key',
        'url_path',
        'name',
        'store_id',
        'visibility'
    ];

    /**
     * @var UrlPersistInterface
     */
    protected $_urlPersist;

    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $_generator;

    /**
     * @var ProductFactory
     */
    protected $_catalogProductFactory;

    /**
     * @var array
     */
    protected $_productsToUpdate = [];

    /**
     * @var Data
     */
    private $rfHelper;

    /**
     * Url constructor.
     * @param Context $context
     * @param ProductFactory $catalogFactory
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param UrlPersistInterface $urlPersist
     * @param Data $rfHelper
     */
    public function __construct(
        Context $context,
        ProductFactory $catalogFactory,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        Data $rfHelper
    )
    {
        $this->_catalogProductFactory = $catalogFactory;
        $this->_urlPersist = $urlPersist;
        $this->_generator = $productUrlRewriteGenerator;

        parent::__construct($context);
        $this->rfHelper = $rfHelper;
    }

    /**
     * @param $productId
     * @param array $productData
     */
    public function addProductIdForRewriteUpdate($productId, array $productData)
    {
        $this->_productsToUpdate[$productId] = $productData;
    }

    /**
     * @param int|null $storeId
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function updateProductsUrlRewrites($storeId = null)
    {
        foreach ($this->_productsToUpdate as $productId => $productData) {
            $this->refreshProductRewrite($productId, $productData);
            if ($productData['store_id'] !== 0) {
                $productData['store_id'] = 0;
                $this->refreshProductRewrite($productId, $productData);
            }
        }
    }

    /**
     * @param $productId
     * @param array $productData
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function refreshProductRewrite($productId, array $productData = [])
    {
        /** @var Product $product */
        $product = $this->_catalogProductFactory->create();
//        $field = 'entity_id';
//        if($this->rfHelper->hasMageFeature(RfProduct::ROW_ID)){
//            $field = RfProduct::ROW_ID;
//        }
        $product->setId($productId);
        foreach ($this->vitalForGenerationFields as $field) {
            if (isset($productData[$field])) {
                $product->setData($field, $productData[$field]);
            }
        }

        $this->_urlPersist->deleteByData(
            [
                UrlRewrite::ENTITY_ID => $product->getId(),
                UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                UrlRewrite::REDIRECT_TYPE => 0,
                UrlRewrite::STORE_ID => $product->getStoreId()
            ]
        );

        $this->_urlPersist->replace($this->_generator->generate($product));
    }
}
