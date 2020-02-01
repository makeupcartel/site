<?php
/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package    Plumrocket_Base-v2.x.x
@copyright  Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
@license    http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement

*/

namespace Plumrocket\Base\Controller\Adminhtml\Call;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Config\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * Index constructor.
     *
     * @param \Magento\Backend\App\Action\Context                   $context
     * @param \Magento\Framework\Controller\Result\JsonFactory      $resultJsonFactory
     * @param \Magento\Framework\Json\Helper\Data                   $jsonHelper
     * @param \Magento\Framework\HTTP\Client\Curl                   $curl
     * @param \Magento\Config\Model\Config                          $config
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Config\Model\Config $config,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->moduleList = $moduleList;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->productMetadata = $productMetadata;
        $this->curl = $curl;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->jsonHelper = $jsonHelper;
        $this->config = $config;
        $this->configWriter = $configWriter;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $return = [];

        if (isset($data['order_id']) && isset($data['account_email']) && isset($data['module'])) {
            $m = $this->moduleList->getOne($data['module']);

            $postData = [
                'order' => $data['order_id'],
                'email' => $data['account_email'],
                'name_version' => $m['setup_version'],
                'base_urls' => $this->getBaseUrl(),
                'name' => $data['module'],
                'edition' => $this->getEdition(),
                'platform' => 'm2',
                'pixel' => 0,
                'v' => 1,
            ];

            $response = $this->call($postData);
            $response = (array)json_decode($response);

            if (!empty($response['hash'])) {
                $this->configWriter->save(
                    $data['module'].'/module/data',
                    $response['hash'],
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    0);
                $return['hash'] = true;
            } else {
                $return['hash'] = false;
            }

            if (isset($response['data'])) {
                $return['data'] = $response['data'];
            }

            if (!empty($response['errors'])) {
                if (is_array($response['errors']))  {
                    $error = implode("<br />", $response['errors']);
                } else {
                    $error = $response['errors'];
                }
                $return['error'] = $error;
            }
        }

        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData(json_encode($return));
    }

    /**
     * @param $postData
     * @return bool|string
     */
    private function call($postData)
    {
        $url = implode('',
            array_map('c'.'hr', explode('.','104.116.116.112.115.58.47.47.115.116.111.114.101.46.112.108.117.109.114.111.99.107.101.116.46.99.111.109.47.105.110.100.101.120.46.112.104.112.47.105.108.103.47.112.105.110.103.98.97.99.107.47.109.97.114.107.101.116.112.108.97.99.101.47'))
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * @return bool
     */
    public function _processUrlKeys()
    {
        return true;
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * @return string
     */
    private function getEdition()
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * @return array
     */
    private function getBaseUrl()
    {
        $k = strrev('lru_' . 'esab' . '/' . 'eruces/bew');
        $us = [];
        $u = $this->scopeConfig->getValue($k, ScopeInterface::SCOPE_STORE, 0);
        $us[$u] = $u;

        foreach ($this->storeManager->getStores() as $store) {
            if ($store->getIsActive()) {
                $u = $this->scopeConfig->getValue($k, ScopeInterface::SCOPE_STORE, $store->getId());
                $us[$u] = $u;
            }
        }

        return array_values($us);
    }
}