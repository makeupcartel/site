<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Ui\StopWord\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Amasty\ElasticSearch\Api\Data\StopWordInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Amasty\ElasticSearch\Model\ResourceModel\StopWord\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\ElasticSearch\Model\ResourceModel\StopWord\CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $result = [];

        foreach ($this->collection as $item) {
            $data = [
                StopWordInterface::STOP_WORD_ID => $item->getId(),
                StopWordInterface::TERM => $item->getTerm(),
                StopWordInterface::STORE_ID => $item->getStoreId(),
            ];

            $result[$item->getId()] = $data;
        }

        return $result;
    }
}
