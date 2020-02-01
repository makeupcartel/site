<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\Indexer\Category\Fulltext\Action;

use Amasty\Xsearch\Model\Indexer\Category\Fulltext;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\ResourceConnection;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Full
{
    /**
     * Scope identifier
     */
    const SCOPE_FIELD_NAME = 'scope';

    /**
     * Searchable attributes cache
     *
     * @var \Magento\Eav\Model\Entity\Attribute[]
     */
    private $searchableAttributes;

    /**
     * Index values separator
     *
     * @var string
     */
    protected $separator = ' | ';

    /**
     * Array of \DateTime objects per store
     *
     * @var \DateTime[]
     */
    protected $dates = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory
     */
    private $categoryAttributeCollectionFactory;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Indexer\SaveHandler\IndexerInterface
     */
    private $indexHandler;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var \Magento\Framework\Search\Request\Config
     */
    private $searchRequestConfig;

    /**
     * @var \Magento\Framework\Search\Request\DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var \Amasty\Xsearch\Model\Indexer\Category\Fulltext\Action\IndexIteratorFactory
     */
    private $iteratorFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(
        ResourceConnection $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Search\Request\Config $searchRequestConfig,
        \Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory $categoryAttributeCollectionFactory,
        \Amasty\Xsearch\Model\Indexer\Category\IndexerHandlerFactory $indexHandlerFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Search\Request\DimensionFactory $dimensionFactory,
        \Magento\Framework\Indexer\ConfigInterface $indexerConfig,
        \Amasty\Xsearch\Model\Indexer\Category\Fulltext\Action\IndexIteratorFactory $indexIteratorFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();

        $this->eavConfig = $eavConfig;
        $this->searchRequestConfig = $searchRequestConfig;
        $this->categoryAttributeCollectionFactory = $categoryAttributeCollectionFactory;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $configData = $indexerConfig->getIndexer(Fulltext::INDEXER_ID);
        $this->indexHandler = $indexHandlerFactory->create(['data' => $configData]);
        $this->dateTime = $dateTime;
        $this->localeResolver = $localeResolver;
        $this->localeDate = $localeDate;
        $this->dimensionFactory = $dimensionFactory;
        $this->iteratorFactory = $indexIteratorFactory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Rebuild whole fulltext index for all stores
     *
     * @return void
     */
    public function reindexAll()
    {
        $storeIds = array_keys($this->storeManager->getStores());
        foreach ($storeIds as $storeId) {
            $this->cleanIndex($storeId);
            $this->rebuildStoreIndex($storeId);
        }

        $this->searchRequestConfig->reset();
    }

    /**
     * Return validated table name
     *
     * @param string|string[] $table
     * @return string
     */
    protected function getTable($table)
    {
        return $this->resource->getTableName($table);
    }
    /**
     * Regenerate search index for specific store
     *
     * @param int $storeId Store View Id
     * @param int|array $categoriesIds Category Entity Id
     * @return \Generator
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function rebuildStoreIndex($storeId, $categoriesIds = null)
    {
        $isActive = $this->getSearchableAttribute('is_active');

        if(!$categoriesIds) {
            $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();
            if ($rootCategoryId) {
                $default = current($this->storeManager->getStores());
                $rootCategoryId =$default->getRootCategoryId();
            }
            $rootCategory = $this->categoryRepository->get($this->storeManager->getStore($storeId)->getRootCategoryId());
            $categoriesIds = $rootCategory->setStoreId($storeId)->getAllChildren(true);
        }

        return $this->iteratorFactory->create([
            'storeId' => $storeId,
            'categoriesIds' => $categoriesIds,
            'isActive' => $isActive,
            'actionFull' => $this,
            'fields' => [
                'int' => array_keys($this->getSearchableAttributes('int')),
                'varchar' => array_keys($this->getSearchableAttributes('varchar')),
                'text' => array_keys($this->getSearchableAttributes('text')),
                'decimal' => array_keys($this->getSearchableAttributes('decimal')),
                'datetime' => array_keys($this->getSearchableAttributes('datetime')),
            ]
        ]);
    }

    /**
     * Clean search index data for store
     *
     * @param int $storeId
     * @return void
     */
    protected function cleanIndex($storeId)
    {
        $dimension = $this->dimensionFactory->create(['name' => self::SCOPE_FIELD_NAME, 'value' => $storeId]);
        $this->indexHandler->cleanIndex([$dimension]);
    }

    /**
     * Retrieve EAV Config Singleton
     *
     * @return \Magento\Eav\Model\Config
     */
    protected function getEavConfig()
    {
        return $this->eavConfig;
    }

    /**
     * Retrieve searchable attributes
     *
     * @param string $backendType
     * @return \Magento\Eav\Model\Entity\Attribute[]
     */
    public function getSearchableAttributes($backendType = null)
    {
        if (null === $this->searchableAttributes) {
            $this->searchableAttributes = [];

            $categoryAttributes = $this->categoryAttributeCollectionFactory->create();

            $categoryAttributes->addFieldToFilter('attribute_code', [
                'in' => [
                    'name',
                    'description',
                    'meta_description',
                    'meta_keywords',
                    'meta_title',
                    'is_active'
                ]
            ]);
            /** @var \Magento\Eav\Model\Entity\Attribute[] $attributes */
            $attributes = $categoryAttributes->getItems();

            $entity = $this->getEavConfig()->getEntityType(Category::ENTITY)->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }

            $this->searchableAttributes = $attributes;
        }

        if ($backendType !== null) {
            $attributes = [];
            foreach ($this->searchableAttributes as $attributeId => $attribute) {
                if ($attribute->getBackendType() == $backendType) {
                    $attributes[$attributeId] = $attribute;
                }
            }

            return $attributes;
        }

        return $this->searchableAttributes;
    }

    /**
     * Retrieve searchable attribute by Id or code
     *
     * @param int|string $attribute
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    public function getSearchableAttribute($attribute)
    {
        $attributes = $this->getSearchableAttributes();
        if (is_numeric($attribute)) {
            if (isset($attributes[$attribute])) {
                return $attributes[$attribute];
            }
        } elseif (is_string($attribute)) {
            foreach ($attributes as $attributeModel) {
                if ($attributeModel->getAttributeCode() == $attribute) {
                    return $attributeModel;
                }
            }
        }

        return $this->getEavConfig()->getAttribute(Category::ENTITY, $attribute);
    }

    /**
     * Returns expression for field unification
     *
     * @param string $field
     * @param string $backendType
     * @return \Zend_Db_Expr
     */
    protected function unifyField($field, $backendType = 'varchar')
    {
        if ($backendType == 'datetime') {
            $expr = $this->connection->getDateFormatSql($field, '%Y-%m-%d %H:%i:%s');
        } else {
            $expr = $field;
        }
        return $expr;
    }
}
