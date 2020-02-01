<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Client;

/**
 * Interface ClientRepositoryInterface
 */
interface ClientRepositoryInterface
{
    /**
     * @return Elasticsearch
     */
    public function get();
}
