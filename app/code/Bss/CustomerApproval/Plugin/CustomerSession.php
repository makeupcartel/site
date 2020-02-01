<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CustomerApproval
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerApproval\Plugin;

use Bss\CustomerApproval\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http as responseHttp;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Bss\CustomerApproval\Model\ResourceModel\Options;
use Magento\Framework\Registry as CoreRegistry;

class CustomerSession
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var responseHttp
     */
    protected $response;

    /**
     * @var responseHttp
     */
    protected $optionModel;

    /**
     * @var CoreRegistry
     */
    protected $registry;

    /**
     * CustomerSession constructor.
     * @param Data $helper
     * @param Context $context
     * @param responseHttp $response
     * @param Options $optionModel
     * @param CoreRegistry $registry
     */
    public function __construct(
        Data $helper,
        Context $context,
        responseHttp $response,
        Options $optionModel,
        CoreRegistry $registry
    ) {
        $this->helper = $helper;
        $this->messageManager = $context->getMessageManager();
        $this->url = $context->getUrl();
        $this->response = $response;
        $this->optionModel = $optionModel;
        $this->registry = $registry;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @param $customer
     * @return $this|mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetCustomerDataAsLoggedIn(
        $subject,
        \Closure $proceed,
        $customer
    ) {
        if ($this->helper->isEnable()) {
            try {
                $customerAttr = $customer->getCustomAttribute('activasion_status');
                $requestType = $this->registry->registry('bss_check_request_type');
                if ($customerAttr) {
                    $customerValue = (int) $customerAttr->getValue();
                    $pending = (int) $this->optionModel->getStatusValue('Pending')['option_id'];
                    $disapprove = (int) $this->optionModel->getStatusValue('Disapproved')['option_id'];
                    if ($customerValue == $pending) {
                        $message = $this->helper->getPendingMess();
                        $loginUrl = $this->url->getUrl('customer/account/login');
                        $this->getMessage($requestType, $message);
                        $this->response->setRedirect($loginUrl);
                        return $this;
                    } elseif ($customerValue == $disapprove) {
                        $message = $this->helper->getDisapproveMess();
                        $loginUrl = $this->url->getUrl('customer/account/login');
                        $this->getMessage($requestType, $message);
                        $this->response->setRedirect($loginUrl);
                        return $this;
                    }
                    return $proceed($customer);
                }
            } catch (\Exception $e) {
                return $proceed();
            }
        }
        return $proceed($customer);
    }

    /**
     * @param $requestType
     * @param $message
     * @return $this
     */
    protected function getMessage($requestType, $message)
    {
        if ($requestType === "login") {
            $this->messageManager->addErrorMessage($message);
        } elseif ($requestType === "create") {
            $this->messageManager->addSuccessMessage($message);
        }
        return $this;
    }
}
