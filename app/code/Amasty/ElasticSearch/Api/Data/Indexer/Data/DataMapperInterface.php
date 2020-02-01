<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Api\Data\Indexer\Data;

interface DataMapperInterface
{
    const ENTITY_ID_FIELD_NAME = 'entity_id';

    /**
     * @param array $indexData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function map(array $indexData, $storeId, array $context = []);
}
