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
namespace Bss\CustomerApproval\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\UrlInterface;
use Bss\CustomerApproval\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeStatusObserver implements ObserverInterface
{
    /**
     * @var Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $helper;

    /**
     * @var Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * UpgradeStatusObserver constructor.
     * @param CustomerRepositoryInterface $customerRepository
     * @param UrlInterface $url
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        UrlInterface $url,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->url = $url;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $customer = $observer->getCustomer();
            $customerEmail = $customer->getEmail();
            $emailTemplate = $this->helper->getAdminEmailTemplate();

            if ($this->helper->isEnableAdminEmail()) {
                $this->sendEmail($customerEmail, $emailTemplate);
            }
            if ($this->helper->isAutoApproval()) {
                $value = $this->helper->getApproveValue();
                $customer->setCustomAttribute("activasion_status", $value);
                $this->customerRepository->save($customer);
            } else {
                $value = $this->helper->getPendingValue();
                $customer->setCustomAttribute("activasion_status", $value);
                $this->customerRepository->save($customer);
            }
        }
    }

    /**
     * @param string $customerEmail
     * @param string $emailTemplate
     * @return mixed
     */
    protected function sendEmail($customerEmail, $emailTemplate)
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $recipients = $this->helper->getAdminEmail();
            $recipients = str_replace(' ', '', $recipients);
            $recipients = (explode(',', $recipients));
            $email = $this->helper->getAdminEmailSender();
            $emailValue = 'trans_email/ident_'.$email.'/email';
            $emailName = 'trans_email/ident_'.$email.'/name';
            $emailSender = $this->scopeConfig->getValue($emailValue, ScopeInterface::SCOPE_STORE);
            $emailNameSender = $this->scopeConfig->getValue($emailName, ScopeInterface::SCOPE_STORE);
            $this->inlineTranslation->suspend();
            $sender = [
                'name' => $this->escaper->escapeHtml($emailNameSender),
                'email' => $this->escaper->escapeHtml($emailSender),
                ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(
                    [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                    ]
                )
            ->setTemplateVars([
                    'varEmail'  => $customerEmail,
                ])
            ->setFrom($sender)
            ->addTo($recipients)
            ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            //do nothing
        }
    }
}

