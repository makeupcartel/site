<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Api\Data\Indexer\Structure;

interface IndexBuilderInterface
{
    const MAX_RESULT_COUNT = 1000000;
    const MAX_FIELDS_COUNT = 1000000;

    /**
     * @return array
     */
    public function build();

    /**
     * @param int $storeId
     * @return \Amasty\ElasticSearch\Api\Data\Indexer\Structure\IndexBuilderInterface;
     */
    public function setStoreId($storeId);
}
