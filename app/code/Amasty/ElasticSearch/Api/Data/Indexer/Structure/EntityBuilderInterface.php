<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Api\Data\Indexer\Structure;

interface EntityBuilderInterface
{
    /**
     * @return array
     */
    public function buildEntityFields();
}
