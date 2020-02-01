<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Api\Data\Indexer\Structure;

interface AnalyzerBuilderInterface
{
    /**
     * @param int $storeId
     * @return array
     */
    public function build($storeId);
}
