<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Client;

use Amasty\ElasticSearch\Model\Client\ElasticsearchFactory;
use Amasty\ElasticSearch\Model\Config;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var Elasticsearch
     */
    private $client;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ElasticsearchFactory
     */
    private $elasticsearchFactory;

    public function __construct(
        Config $config,
        ElasticsearchFactory $elasticsearchFactory
    ) {
        $this->config = $config;
        $this->elasticsearchFactory = $elasticsearchFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if ($this->client == null) {
            $this->client = $this->elasticsearchFactory->create(['options' => $this->config->prepareConnectionData()]);
        }

        return $this->client;
    }
}
