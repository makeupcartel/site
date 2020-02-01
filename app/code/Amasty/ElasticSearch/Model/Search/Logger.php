<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search;

use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Amasty\Base\Debug\Log;

class Logger
{
    const LOG_FILE_NAME_DEFAULT = 'amasty_elastic.log';
    const LOG_FILE_NAME_BUCKETS = 'amasty_elastic_buckets.log';
    const LOG_DEPTH_ARRAY = 10;
    const LOG_DEPTH_OBJECT = 10;

    /**
     * @var \Amasty\ElasticSearch\Model\Config
     */
    private $config;

    /**
     * @var \Amasty\ElasticSearch\Model\Config\Backend\Debug
     */
    private $debugConfig;

    public function __construct(
        \Amasty\ElasticSearch\Model\Config $config,
        \Amasty\ElasticSearch\Model\Config\Backend\Debug $debugConfig
    ) {
        $this->config = $config;
        $this->debugConfig = $debugConfig;
    }

    /**
     * @param RequestInterface $request
     * @param QueryResponse $response
     * @param array $rawRequestQuery
     * @param array $rawResponseQuery
     * @return $this
     */
    public function log(
        RequestInterface $request = null,
        QueryResponse $response = null,
        array $rawRequestQuery = [],
        array $rawResponseQuery = []
    ) {
        if ($this->isLogActive()) {
            Log::setLogFile(self::LOG_FILE_NAME_DEFAULT);
            Log::setArrayDepthLevel(self::LOG_DEPTH_ARRAY);
            Log::setObjectDepthLevel(self::LOG_DEPTH_OBJECT);
            $buckets = $request->getAggregation();
            if (count($buckets) > 1) {
                $this->logRequest($request, $response, $rawRequestQuery, $rawResponseQuery);
            } elseif ($this->logAllRequests()) {
                Log::setLogFile(self::LOG_FILE_NAME_BUCKETS);
                $this->logRequest($request, $response, $rawRequestQuery, $rawResponseQuery);
            }
        }

        return $this;
    }

    /**
     * @param RequestInterface $request
     * @param QueryResponse $response
     * @param array $rawRequestQuery
     * @param array $rawResponseQuery
     * @return $this
     */
    private function logRequest(
        RequestInterface $request = null,
        QueryResponse $response = null,
        array $rawRequestQuery = [],
        array $rawResponseQuery = []
    ) {
        if ($this->logRequestObject()) {
            Log::execute(['Request Object' => $request]);
        }

        if ($this->logRawRequest()) {
            Log::execute(['Raw Request' => $rawRequestQuery]);
        }

        if ($this->logRawResponse()) {
            Log::execute(['Raw Response' => $rawResponseQuery]);
        }

        if ($this->logResponseObject()) {
            Log::execute(['Response Object' => $response]);
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function logAllRequests()
    {
        return (bool)$this->config->getDebugConfig('log_buckets_alert_requests');
    }

    /**
     * @return bool
     */
    private function isLogActive()
    {
        if ($value = $this->config->getDebugConfig('use_debug')) {
            return $this->debugConfig->isActive($value);
        }

        return false;
    }

    /**
     * @return bool
     */
    private function logRequestObject()
    {
        return (bool)$this->config->getDebugConfig('log_request_object');
    }

    /**
     * @return bool
     */
    private function logRawRequest()
    {
        return (bool)$this->config->getDebugConfig('log_raw_request');
    }

    /**
     * @return bool
     */
    private function logRawResponse()
    {
        return (bool)$this->config->getDebugConfig('log_raw_response');
    }

    /**
     * @return bool
     */
    private function logResponseObject()
    {
        return (bool)$this->config->getDebugConfig('log_response_object');
    }
}
