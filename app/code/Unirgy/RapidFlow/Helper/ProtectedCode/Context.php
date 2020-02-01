<?php
/**
 * Created by pp
 * @project magento202
 */

namespace Unirgy\RapidFlow\Helper\ProtectedCode;

use Magento\Catalog\Helper\Data as CatalogHelperData;
use Magento\Catalog\Helper\Product\Flat\Indexer;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product\Image;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full as FullTextIndexer;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Indexer\Model\Indexer as IndexerModelIndexer;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Psr\Log\LoggerInterface;
use Unirgy\RapidFlow\Model\CatalogRule;
use Unirgy\RapidFlow\Model\Config as ModelConfig;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product\Collection;
use Unirgy\RapidFlow\Model\ResourceModel\CatalogRule\Collection as CatalogRuleCollection;

class Context
{
    public $scopeConfig;

    public $productCollection;

    public $productModel;

    public $catalogHelper;

    public $productType;

    public $logger;

    public $modelStockStatus;

    public $productMediaConfig;

    public $catalogRuleCollection;

    public $rapidFlowCatalogrule;

    public $indexerRegistry;

    public $modelProductAction;

    public $catalogStockConfiguration;

    public $fullTextIndexer;

    public $productFlatIndexHelper;

    public $productFlatIndexState;

    public $modelProductImage;

    /**
     * @var ModelConfig
     */
    public $rapidFlowConfig;

    /**
     * @var \Unirgy\RapidFlow\Model\ResourceModel\Profile
     */
    public $db;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    public $formatInterface;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * @var \Unirgy\RapidFlow\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Eav\Model\Config
     */
    public $eavConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteFactory
     */
    public $writeFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    public $eventManager;

    /**
     * @var CategoryUrlRewriteGenerator
     */
    public $categoryUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    public $urlPersist;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    public $categoryFactory;

    /**
     * @var \Unirgy\RapidFlow\Helper\Url
     */
    public $urlHelper;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    public $websiteRepository;

    /**
     * @var \Magento\Indexer\Model\Config
     */
    public $indexerConfig;

    /**
     * @var \Unirgy\RapidFlow\Helper\ImageCache
     */
    public $imageCacheHelper;

    /**
     * Context constructor.
     * @param FullTextIndexer $fullTextIndexer
     * @param ScopeConfigInterface $scopeConfig
     * @param Collection $catalogProductCollection
     * @param Product $catalogProduct
     * @param CatalogHelperData $catalogHelperData
     * @param Type $modelProductType
     * @param LoggerInterface $psrLogLoggerInterface
     * @param Status $modelStockStatus
     * @param MediaConfig $productMediaConfig
     * @param CatalogRuleCollection $resourceModelCatalogruleCollection
     * @param CatalogRule $rapidFlowModelCatalogrule
     * @param IndexerRegistry $indexerRegistry
     * @param Action $modelProductAction
     * @param Image $productImage
     * @param State $productFlatState
     * @param Indexer $flatIndexHelper
     * @param StockConfigurationInterface $stockConfiguration
     * @param ModelConfig $rapidFlowConfig
     * @param \Unirgy\RapidFlow\Model\ResourceModel\Profile $db
     * @param \Magento\Framework\Locale\FormatInterface $formatInterface
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Unirgy\RapidFlow\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Filesystem\Directory\WriteFactory $writeFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Unirgy\RapidFlow\Helper\Url $urlHelper
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Indexer\Model\Config $indexerConfig
     * @param \Unirgy\RapidFlow\Helper\ImageCache $imageCacheHelper
     */
    public function __construct(
        FullTextIndexer $fullTextIndexer,
        ScopeConfigInterface $scopeConfig,
        Collection $catalogProductCollection,
        Product $catalogProduct,
        CatalogHelperData $catalogHelperData,
        Type $modelProductType,
        LoggerInterface $psrLogLoggerInterface,
        Status $modelStockStatus,
        MediaConfig $productMediaConfig,
        CatalogRuleCollection $resourceModelCatalogruleCollection,
        CatalogRule $rapidFlowModelCatalogrule,
        IndexerRegistry $indexerRegistry,
        Action $modelProductAction,
        Image $productImage,
        State $productFlatState,
        Indexer $flatIndexHelper,
        StockConfigurationInterface $stockConfiguration,
        ModelConfig $rapidFlowConfig,
        \Unirgy\RapidFlow\Model\ResourceModel\Profile $db,
        \Magento\Framework\Locale\FormatInterface $formatInterface,
        \Magento\Framework\Filesystem $filesystem,
        \Unirgy\RapidFlow\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Filesystem\Directory\WriteFactory $writeFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Unirgy\RapidFlow\Helper\Url $urlHelper,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Indexer\Model\Config $indexerConfig,
        \Unirgy\RapidFlow\Helper\ImageCache $imageCacheHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->productCollection = $catalogProductCollection;
        $this->productModel = $catalogProduct;
        $this->catalogHelper = $catalogHelperData;
        $this->productType = $modelProductType;
        $this->logger = $psrLogLoggerInterface;
        $this->modelStockStatus = $modelStockStatus;
        $this->productMediaConfig = $productMediaConfig;
        $this->catalogRuleCollection = $resourceModelCatalogruleCollection;
        $this->rapidFlowCatalogrule = $rapidFlowModelCatalogrule;
        $this->indexerRegistry = $indexerRegistry;
        $this->modelProductAction = $modelProductAction;
        $this->catalogStockConfiguration = $stockConfiguration;
        $this->fullTextIndexer = $fullTextIndexer;

        $this->productFlatIndexHelper = $flatIndexHelper;
        $this->productFlatIndexState = $productFlatState;
        $this->modelProductImage = $productImage;
        $this->rapidFlowConfig = $rapidFlowConfig;
        $this->db = $db;
        $this->formatInterface = $formatInterface;
        $this->filesystem = $filesystem;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->eavConfig = $eavConfig;
        $this->writeFactory = $writeFactory;
        $this->eventManager = $eventManager;
        $this->urlHelper = $urlHelper;
        $this->websiteRepository = $websiteRepository;
        $this->indexerConfig = $indexerConfig;
        $this->imageCacheHelper = $imageCacheHelper;
    }
}
