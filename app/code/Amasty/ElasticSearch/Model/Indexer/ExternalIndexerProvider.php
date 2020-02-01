<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer;

class ExternalIndexerProvider
{
    /**
     * @var array
     */
    private $sources;

    public function __construct(
        array $sources = []
    ) {
        $this->sources = $sources;
    }

    /**
     * @param $storeId
     * @return array
     */
    public function getDocuments($storeId)
    {
        $documents = [];
        foreach ($this->sources as $indexType => $source) {
            $documents[$indexType] = $source->get($storeId);
        }

        return $documents;
    }
}
