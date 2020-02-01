<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Data;

use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperInterface;
use Amasty\ElasticSearch\Api\Data\Indexer\Data\DataMapperResolverInterface;

class DataMapperResolver implements DataMapperResolverInterface
{
    const DEFAULT_DATA_INDEXER = 'catalogsearch_fulltext';
    const INDEXER_ID = 'indexer_id';

    /**
     * @var array DataMapperInterface[]
     */
    private $mappers = [];

    public function __construct(array $dataMappers = [])
    {
        foreach ($dataMappers as $type => $mappers) {
            $this->mappers[$type] = [];
            foreach ($mappers as $mapper) {
                $this->mappers[$type][] = $mapper;
            }
        }
    }

    /**
     * @param array $indexData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function mapEntityData(array $indexData, $storeId, $context = [])
    {
        $data = [];
        $indexerId = isset($context[self::INDEXER_ID]) ? $context[self::INDEXER_ID] : self::DEFAULT_DATA_INDEXER;
        if (isset($this->mappers[$indexerId]) && is_array($this->mappers[$indexerId])) {
            foreach ($this->mappers[$indexerId] as $mapper) {
                /**
                 * @var DataMapperInterface $mapper
                 */
                $mappedData = $mapper->map($indexData, $storeId, $context);
                foreach ($mappedData as $entityId => $entityData) {
                    if (isset($data[$entityId])) {
                        $data[$entityId] = array_merge($data[$entityId], $entityData);
                    } else {
                        $data[$entityId] = $entityData;
                    }
                }
            }
        }
        return $data;
    }
}
