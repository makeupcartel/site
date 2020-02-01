<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Ui\Synonym\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;
use Amasty\ElasticSearch\Api\Data\SynonymInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as MagentoDataProvider;

class DataProvider extends MagentoDataProvider
{
    /**
     * @param SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $result = [
            'items'        => [],
            'totalRecords' => $searchResult->getTotalCount(),
        ];

        /** @var SynonymInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $data = [
                SynonymInterface::SYNONYM_ID => $item->getId(),
                SynonymInterface::TERM => $item->getTerm(),
                SynonymInterface::STORE_ID => $item->getStoreId(),
            ];

            $result['items'][] = $data;
        }

        return $result;
    }
}
