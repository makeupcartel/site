<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\Config\Checker;

use Amasty\ElasticSearch\Model\Client\ElasticsearchFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\StripTags;
use Magento\Backend\App\Action;
use Amasty\ElasticSearch\Model\Config;

class Connection extends Action
{
    const ADMIN_RESOURCE = 'Amasty_ElasticSearch::config';

    /**
     * @var ElasticsearchFactory
     */
    private $elasticsearchFactory;

    /**
     * @var \Amasty\ElasticSearch\Model\Config
     */
    private $config;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var StripTags
     */
    private $tagFilter;

    public function __construct(
        Context $context,
        \Amasty\ElasticSearch\Model\Config $config,
        ElasticsearchFactory $elasticsearchFactory,
        JsonFactory $resultJsonFactory,
        StripTags $tagFilter
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->tagFilter = $tagFilter;
        $this->elasticsearchFactory = $elasticsearchFactory;
        $this->config = $config;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];
        $options = $this->getRequest()->getParams();

        try {
            if (empty($options['engine']) || $options['engine'] !== Config::ELASTIC_SEARCH_ENGINE) {
                throw new LocalizedException(
                    __('Test connection can be applied only for Amasty Elastic Search engine.')
                );
            }
            $connectionData = $this->config->prepareConnectionData($options);
            $result['success'] = $this->elasticsearchFactory->create(['options' => $connectionData])->ping();
        } catch (LocalizedException $e) {
            $result['errorMessage'] = $e->getMessage();
        } catch (\Exception $e) {
            $result['errorMessage'] = $this->tagFilter->filter(__($e->getMessage()));
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($result);
    }
}
