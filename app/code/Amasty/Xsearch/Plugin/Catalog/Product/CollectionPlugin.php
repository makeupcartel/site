<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Plugin\Catalog\Product;

use Amasty\Shopby\Model\ResourceModel\Fulltext\Collection as ShopbyCollection;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection as CatalogSearchCollection;
use Magento\Store\Model\ScopeInterface;

class CollectionPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var array
     */
    private $searchModules = [
        'catalogsearch',
        'amasty_xsearch'
    ];

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    private $stockHelper;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\CatalogInventory\Helper\Stock $stockHelper
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->moduleManager = $moduleManager;
        $this->stockHelper = $stockHelper;
    }

    /**
     * @param CatalogSearchCollection|ShopbyCollection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad($subject, $printQuery = false, $logQuery = false)
    {
        if (in_array($this->request->getModuleName(), $this->searchModules)
            && !$subject->isLoaded() && $this->isEnabled()
        ) {
            $fromTables = $subject->getSelect()->getPart('from');
            if (!isset($fromTables['stock_status_index'])) {
                $this->stockHelper->addIsInStockFilterToCollection($subject);
                $fromTables = $subject->getSelect()->getPart('from');
            }

            $stockStatus = $this->moduleManager->isEnabled('Magento_Inventory') &&
                $fromTables['stock_status_index']['tableName'] !=
                $subject->getResource()->getTable('cataloginventory_stock_status')
                ? 'is_salable'
                : 'stock_status';
            $subject->getSelect()->order(
                'stock_status_index.' . $stockStatus . ' ' . CatalogSearchCollection::SORT_ORDER_DESC
            );
            $orders = $subject->getSelect()->getPart(\Zend_Db_Select::ORDER);
            // move from the last to the the first position
            array_unshift($orders, array_pop($orders));
            $subject->getSelect()->setPart(\Zend_Db_Select::ORDER, $orders);
        }

        return [$printQuery, $logQuery];
    }

    /**
     * @return bool
     */
    private function isEnabled()
    {
        return $this->scopeConfig->getValue(
            'amasty_xsearch/product/out_of_stock_last',
            ScopeInterface::SCOPE_STORE
        );
    }
}
