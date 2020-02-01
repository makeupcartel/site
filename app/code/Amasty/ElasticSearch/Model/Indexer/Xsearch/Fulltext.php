<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\Xsearch;

use Magento\Framework\Indexer\ActionInterface;
use Amasty\ElasticSearch\Model\Client\Elasticsearch as Client;

class Fulltext  implements ActionInterface
{
    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    private $cacheContext;

    /**
     * @var Client
     */
    private $elasticClient;

    public function __construct(
        \Magento\Framework\Indexer\CacheContext $cacheContext,
        Client $elasticClient
    ) {
        $this->cacheContext = $cacheContext;
        $this->elasticClient = $elasticClient;
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        try {
            $this->elasticClient->saveExternal();
        } catch (\Exception $e) {

        }
    }

    /**
     * @inheritdoc
     */
    public function executeRow($id)
    {
        try {
            $this->executeFull();
        } catch (\Exception $e) {

        }
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $ids)
    {
        try {
            $this->executeFull();
        } catch (\Exception $e) {

        }
    }
}
