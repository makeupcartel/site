<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Sales\Category;

use Amasty\Reports\Traits\Filters;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Sales\Category
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    use Filters;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    /**
     * @var \Amasty\Reports\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var \Magento\Catalog\Model\Entity\Attribute
     */
    private $catalogEav;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory                  $entityFactory
     * @param \Psr\Log\LoggerInterface                                          $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface      $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                         $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot
     * @param \Magento\Framework\DB\Helper                                      $coreResourceHelper
     * @param \Magento\Framework\App\RequestInterface                           $request
     * @param \Amasty\Reports\Helper\Data                                       $helper
     * @param \Magento\Catalog\Model\Product                                    $product
     * @param \Magento\Catalog\Model\Entity\AttributeFactory                    $attributeFactory
     * @param \Magento\Eav\Model\Config                                         $eavConfig
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null               $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null         $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $coreResourceHelper,
            $connection,
            $resource
        );
        $this->request = $request;
        $this->catalogEav = $attributeFactory->create();
        $this->eavConfig = $eavConfig;
        $this->helper = $helper;
        $this->product = $product;
    }

    /**
     * @param \Amasty\Reports\Model\ResourceModel\Sales\Category\Grid\Collection $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function joinCategoryTable($collection)
    {
        $entityTypeId = $this->eavConfig
            ->getEntityType(\Magento\Catalog\Api\Data\CategoryAttributeInterface::ENTITY_TYPE_CODE)
            ->getEntityTypeId();
        /** @var \Magento\Catalog\Model\Entity\Attribute $attribute */
        $attribute = $this->catalogEav->loadByCode($entityTypeId, 'name');
        $categoryId = $attribute->getAttributeId();
        $id = $this->product->getResource()->getLinkField();
        $collection->getSelect()
            ->join(
                ['sales_order_item' => $this->getTable('sales_order_item')],
                'sales_order_item.order_id = main_table.entity_id'
            )
            ->join(
                ['catalog_category_product' => $this->getTable('catalog_category_product')],
                'catalog_category_product.product_id = sales_order_item.product_id'
            )
            ->join(
                ['catalog_category_entity_varchar' => $this->getTable('catalog_category_entity_varchar')],
                'catalog_category_entity_varchar.' . $id . ' = catalog_category_product.category_id'
            )
            ->where('sales_order_item.parent_item_id IS NULL')
            ->where('catalog_category_entity_varchar.attribute_id = ?', $categoryId)
        ;
    }

    /**
     * @param $collection
     */
    public function applyBaseFilters($collection)
    {
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS);
        $this->joinCategoryTable($collection);
        $collection->getSelect()->columns(
            [
                'period'       => 'catalog_category_entity_varchar.value',
                'total_orders' => 'COUNT(DISTINCT main_table.entity_id)',
                'total_items'  => 'COUNT(sales_order_item.item_id)',
                'subtotal'     => 'SUM(sales_order_item.base_row_total)',
                'tax'          => 'SUM(sales_order_item.base_tax_amount)',
                'status'       => 'main_table.status',
                'discounts'    => 'SUM(sales_order_item.base_discount_amount)',
                'total'        => 'SUM(sales_order_item.base_row_total_incl_tax)',
                'invoiced'     => 'SUM(sales_order_item.base_row_invoiced)',
                'refunded'     => 'SUM(sales_order_item.base_amount_refunded)',
                'entity_id'    => 'CONCAT(main_table.entity_id,catalog_category_product.category_id,\'' .
                    $this->createUniqueEntity() . '\')'
            ]
        );
    }

    /**
     * @param $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addStoreFilter($collection);
        $this->addGroupBy($collection);
        $this->addStatusFilter($collection);
    }

    /**
     * @param $collection
     */
    public function addGroupBy($collection)
    {
        $collection->getSelect()
            ->group("catalog_category_product.category_id")
        ;
    }
}
