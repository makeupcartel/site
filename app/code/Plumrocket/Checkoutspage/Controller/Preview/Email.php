<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Checkoutspage v2.x.x
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Controller\Preview;

use Magento\Framework\Profiler;
use Plumrocket\Checkoutspage\Helper\Data as HelperData;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;

class Email extends \Plumrocket\Checkoutspage\Controller\Preview
{
    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var \Magento\Framework\Filter\Input\MaliciousCode
     */
    protected $maliciousCode;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Plumrocket\Checkoutspage\Model\Email
     */
    protected $email;

    /**
     * Email constructor.
     *
     * @param \Magento\Framework\App\Action\Context                     $context
     * @param \Magento\Customer\Model\Session                           $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface         $customerRepository
     * @param \Magento\Customer\Api\AccountManagementInterface          $accountManagement
     * @param \Magento\Framework\Registry                               $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface              $translateInline
     * @param \Magento\Framework\Data\Form\FormKey\Validator            $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface        $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory                     $layoutFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface                $quoteRepository
     * @param \Magento\Framework\View\Result\PageFactory                $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory              $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory           $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory          $resultJsonFactory
     * @param \Magento\Sales\Model\OrderFactory                         $orderFactory
     * @param HelperData                                                $helperData
     * @param \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableScopeConfig
     * @param \Magento\Email\Model\TemplateFactory                      $templateFactory
     * @param \Magento\Framework\Filter\Input\MaliciousCode             $maliciousCode
     * @param \Magento\Framework\App\State                              $state
     * @param \Plumrocket\Checkoutspage\Model\Email                     $email
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        HelperData $helperData,
        \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableScopeConfig,
        \Magento\Email\Model\TemplateFactory $templateFactory,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        \Magento\Framework\App\State $state,
        \Plumrocket\Checkoutspage\Model\Email $email
    ) {
        $this->templateFactory  = $templateFactory;
        $this->maliciousCode    = $maliciousCode;
        $this->state = $state;
        $this->email = $email;
        parent::__construct($context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory,
            $orderFactory,
            $helperData,
            $mutableScopeConfig
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $order = $this->_getOrder();

        if (!$order || !$this->_canView()) {
            return $this->_redirectToCart();
        }

        $this->getResponse()->setBody($this->_getTemplateContent());
    }

    /**
     * Retrieve tempaltea content
     * @return string
     */
    protected function _getTemplateContent()
    {
        $storeId = $this->_getOrder()->getStoreId();
        /** @var $template \Magento\Email\Model\Template */
        $template = $this->templateFactory->create();

        $templateContent = $template->getTemplateContent($this->_getConfigPath(), $this->_getVariables($this->_getOrder()));
        $template->setTemplateText($templateContent);

        $template->setTemplateText($this->maliciousCode->filter($template->getTemplateText()));

        Profiler::start('email_template_proccessing');

        $template->emulateDesign($storeId);

        $templateProcessed = $this->state->emulateAreaCode(
            \Magento\Email\Model\AbstractTemplate::DEFAULT_DESIGN_AREA,
            [$template, 'getProcessedTemplate']
        );
        $template->revertDesign();

        if ($template->isPlain()) {
            $templateProcessed = "<pre>" . htmlspecialchars($templateProcessed) . "</pre>";
        }

        Profiler::stop('email_template_proccessing');

        return $templateProcessed;
    }

    /**
     * Get path to configurations
     * @return string
     */
    protected function _getConfigPath()
    {
        if ($this->getRequest()->getParam('type') == 'new') {
            return  HelperData::$configSectionId . '/email/template';
        } else {
            return OrderIdentity::XML_PATH_EMAIL_TEMPLATE;
        }
    }

    /**
     * Get variables for old template
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    protected function _getVariables($order)
    {
        return ($this->getRequest()->getParam('type') == 'new')
            ? $this->email->getNewTemplateVariables($order)
            : $this->email->getOldTemplateVariables($order);
    }
}
