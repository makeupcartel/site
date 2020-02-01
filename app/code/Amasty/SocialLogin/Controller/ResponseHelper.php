<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Controller;

use Amasty\SocialLogin\Model\Source\RedirectType;

class ResponseHelper
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $pageFactory;

    /**
     * @var \Amasty\SocialLogin\Model\ConfigData
     */
    private $configData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;

    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Amasty\SocialLogin\Model\ConfigData $configData,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->pageFactory = $pageFactory;
        $this->configData = $configData;
        $this->url = $url;
        $this->customerSessionFactory = $customerSessionFactory;
    }

    /**
     * @return string
     */
    private function getHeaderLinksContent()
    {
        $page = $this->pageFactory->create(false, ['isIsolated' => true]);
        $page->addHandle('cms_index_index');
        $html = '';
        $header = $page->getLayout()->getBlock('header.links');
        if ($header) {
            $html = $header->toHtml();
        }

        return $html;
    }

    /**
     * @return string
     */
    private function getCustomerName()
    {
        $name = '';
        $customer = $this->getCustomerSession()->getCustomer();
        if ($customer) {
            $name = $customer->getName();
        }

        return $name;
    }

    /**
     * @return array
     */
    public function getRedirectData()
    {
        $type = $this->configData->getConfigValue('general/redirect_type');
        $content = ($type == RedirectType::AJAX_REFRESH) ? $this->getHeaderLinksContent() : '';
        return [
            'content'  => $content,
            'url'      => $this->url->getUrl($this->configData->getConfigValue('general/custom_url')),
            'customer_name' => $this->getCustomerName(),
            'redirect' => $type
        ];
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    private function getCustomerSession()
    {
        return $this->customerSessionFactory->create();
    }
}
