<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Api\Data\Indexer\Data;

interface DataMapperResolverInterface
{
    /**
     * @param int $entityId
     * @param array $indexData
     * @param int $storeId
     * @param array $context
     * @return array
     */
    public function mapEntityData(array $indexData, $storeId, $context = []);
}
