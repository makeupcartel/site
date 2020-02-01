<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Catalog\ByAttributes;

use Amasty\Reports\Traits\Filters;
use Amasty\Reports\Block\Adminhtml\Report\Catalog\ByAttributes\Toolbar;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Amasty\Reports\Model\ResourceModel\Catalog\ByAttributes
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Order\Item\Collection
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

    protected $_idFieldName = '';

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $connection,
            $resource
        );
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * @param $collection
     */
    public function prepareCollection($collection)
    {
        $this->joinEavAttribute($collection);
        $this->joinChilds($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function joinEavAttribute($collection)
    {
        $filters = $this->request->getParam('amreports');
        $eav = isset($filters['eav']) ? $filters['eav'] : 'name';

        if ($eav == Toolbar::ATTRIBUTE_SET) {
            $value = 'eas.attribute_set_name';
            $entityId = 'CONCAT(eas.attribute_set_id,cpe.entity_id,\''.$this->createUniqueEntity().'\')';
            $this->joinAttributeSet($collection);
        } else {
            $value = sprintf('eaov1_%1$s.value', $eav);
            $entityId = sprintf(
                'CONCAT(eaov1_%1$s.value,\'' . $this->createUniqueEntity() . '\')',
                $eav
            );
            $this->joinCustomAttribute($collection, $eav);
            $collection->getSelect()->where(
                sprintf('(eaov1_%1$s.value IS NOT NULL)', $eav)
            );
        }

        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'name' => $value,
                'total_orders' => 'COUNT(DISTINCT main_table.order_id)',
                'items_ordered' => 'COUNT(main_table.qty_ordered)',
                'qty' => 'FLOOR(SUM(main_table.qty_ordered))',
                'total' => 'SUM(IF(soi.total IS NOT NULL AND soi.total != 0, soi.total, main_table.base_row_total))',
                'tax' => 'SUM(main_table.base_tax_amount)',
                'discounts' => 'SUM(IF(soi.base_discount_amount IS NOT NULL AND soi.base_discount_amount != 0, '
                    . 'soi.base_discount_amount, main_table.base_discount_amount))',
                'invoiced' => 'SUM(IF(soi.invoiced IS NOT NULL AND soi.invoiced != 0, '
                    . 'soi.invoiced, main_table.base_row_invoiced))',
                'refunded' => 'SUM(IF(soi.refunded IS NOT NULL AND soi.refunded != 0, '
                    . 'soi.refunded, main_table.base_amount_refunded))',
                'entity_id' => $entityId
            ])->where(
                'main_table.parent_item_id IS NULL'
            );
    }

    /**
     * @param $collection
     */
    private function joinAttributeSet($collection)
    {
        $collection->getSelect()
            ->joinleft(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'cpe.entity_id = main_table.product_id'
            )
            ->joinLeft(
                ['eas' => $this->getTable('eav_attribute_set')],
                'cpe.attribute_set_id = eas.attribute_set_id'
            )
            ->group('cpe.attribute_set_id');
    }

    /**
     * @param $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addStoreFilter($collection);
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return $this|\Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    public function addItem(\Magento\Framework\DataObject $item)
    {
        parent::_addItem($item); // TODO: Change the autogenerated stub
        return $this;
    }

    /**
     * @param AbstractCollection $collection
     */
    private function joinChilds($collection)
    {
        $childsSelect = $this->getConnection()->select()->from(
            $this->getTable('sales_order_item'),
            [
                'total' => 'SUM(base_row_total)',
                'invoiced' => 'SUM(base_row_invoiced)',
                'refunded' => 'SUM(base_amount_refunded)',
                'base_discount_amount' => 'SUM(base_discount_amount)',
                'parent_item_id'
            ]
        )->group(
            'parent_item_id'
        );

        $collection->getSelect()->joinLeft(
            ['soi' => $childsSelect],
            'soi.parent_item_id = main_table.item_id',
            ''
        );
    }
}
