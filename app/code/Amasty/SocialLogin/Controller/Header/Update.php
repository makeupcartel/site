<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Controller\Header;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Update extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Amasty\SocialLogin\Model\ConfigData
     */
    private $configData;

    /**
     * @var \Amasty\SocialLogin\Controller\ResponseHelper
     */
    private $responseHelper;

    public function __construct(
        Context $context,
        \Amasty\SocialLogin\Model\ConfigData $configData,
        \Amasty\SocialLogin\Controller\ResponseHelper $responseHelper,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configData = $configData;
        $this->responseHelper = $responseHelper;
    }

    /**
     * @return ResponseInterface|Json|Redirect|ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $resultPage = $this->resultJsonFactory->create();
            $resultPage->setHttpResponseCode(200);
            $resultPage->setData($this->responseHelper->getRedirectData());
            return $resultPage;
        } else {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }
    }
}
