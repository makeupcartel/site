<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Ui\DataProvider\Listing\Customers\Conversion;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Amasty\Reports\Model\ResourceModel\Customers\Conversion\Grid\CollectionFactory;

/**
 * Class DataProvider
 * @package Amasty\Reports\Ui\DataProvider\Listing\Customers\Conversion
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    const FILTER_VISITORS = 'visitors';
    const FILTER_ORDERS = 'orders';
    const FILTER_CONVERSION = 'conversion';

    /**
     * @var array
     */
    private $havingColumns = [
        'visitors' => 'COUNT(DISTINCT main_table.session_id)',
        'orders' => 'COUNT(DISTINCT orderTable.entity_id)',
        'conversion' => 'ROUND(COUNT(DISTINCT orderTable.entity_id) / COUNT(DISTINCT main_table.session_id) * 100)'
    ];

    /**
     * @var array
     */
    private $havingFilters = [];

    /**
     * @param \Magento\Framework\Api\Filter $filter
     *
     * @return mixed|void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'period') {
            $filter->setField(new \Zend_Db_Expr('CONCAT(\',\',applied_rule_ids,\',\')'));
            $filter->setConditionType('like');
            $filter->setValue('%,' . $filter->getValue() . ',%');
        } elseif (in_array($filter->getField(), array_keys($this->havingColumns))) {
            $this->havingFilters[] = $filter;
            return;
        }

        parent::addFilter($filter);
    }

    /**
     * @inheritdoc
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $operations = [
            'gteq' => '>=',
            'lteq' => '<=',
            'like' => 'like'
        ];

        foreach ($this->havingFilters as $filter) {
            $fieldExpr = $this->havingColumns[$filter->getField()];
            $searchResult->getSelect()->having(
                $fieldExpr . ' ' . $operations[$filter->getConditionType()] . ' "' . $filter->getValue() . '"'
            );
        }

        return parent::searchResultToOutput($searchResult);
    }

    /**
     * @return array
     */
    public function getData()
    {
        $result = parent::getData();

        foreach ($result['items'] as &$orderItem) {
            $orderItem['conversion'] = round($orderItem['conversion']) . '%';
        }

        return $result;
    }
}
