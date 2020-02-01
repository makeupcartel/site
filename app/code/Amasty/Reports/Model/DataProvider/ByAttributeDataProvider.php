<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\DataProvider;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as AbstractDataProvider;

/**
 * Class ByAttributeDataProvider
 * @package Amasty\Reports\Model\DataProvider
 */
class ByAttributeDataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $havingOperations = [];

    /**
     * @param \Magento\Framework\Api\Filter $filter
     * @return mixed|void
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->havingOperations[] = $filter;
    }

    /**
     * @param SearchResultInterface $searchResult
     * @return array
     */
    public function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $operations = [
            'gteq' => '>=',
            'lteq' => '<=',
            'like' => 'like'
        ];
        foreach ($this->havingOperations as $filter) {
            $searchResult->getSelect()->having(
                $filter->getField() . ' ' . $operations[$filter->getConditionType()] . ' "' . $filter->getValue() . '"'
            );
        }

        return parent::searchResultToOutput($searchResult);
    }
}
