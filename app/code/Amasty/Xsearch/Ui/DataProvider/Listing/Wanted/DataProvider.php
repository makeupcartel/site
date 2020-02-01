<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Ui\DataProvider\Listing\Wanted;

use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Amasty\Xsearch\Model\QueryInfo
     */
    private $queryInfo;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Amasty\Xsearch\Model\QueryInfo $queryInfo,
        \Amasty\Xsearch\Model\ResourceModel\UserSearch\Collection $collection,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->queryInfo = $queryInfo;
        $this->collection = $collection;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getData()
    {
        $mostWantedValues = $this->queryInfo->getMostWantedQueries();

        $data = [
            'totalRecords' => count($mostWantedValues)
        ];

        foreach ($mostWantedValues as $value) {
            $data['items'][] = $value->getData();
        }

        return $data;
    }
}
