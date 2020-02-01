<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Ui\StopWord\Listing;

use Magento\Framework\Api\Search\SearchResultInterface;
use Amasty\ElasticSearch\Api\Data\StopWordInterface;
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

        /** @var StopWordInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $data = [
                StopWordInterface::STOP_WORD_ID => $item->getId(),
                StopWordInterface::TERM => $item->getTerm(),
                StopWordInterface::STORE_ID => $item->getStoreId(),
            ];

            $result['items'][] = $data;
        }

        return $result;
    }
}
