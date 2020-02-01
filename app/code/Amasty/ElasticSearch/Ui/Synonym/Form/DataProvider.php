<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Ui\Synonym\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Amasty\ElasticSearch\Api\Data\SynonymInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var \Amasty\ElasticSearch\Model\ResourceModel\Synonym\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\ElasticSearch\Model\ResourceModel\Synonym\CollectionFactory $collectionFactory,
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
                SynonymInterface::SYNONYM_ID => $item->getId(),
                SynonymInterface::TERM => $item->getTerm(),
                SynonymInterface::STORE_ID => $item->getStoreId(),
            ];

            $result[$item->getId()] = $data;
        }

        return $result;
    }
}
