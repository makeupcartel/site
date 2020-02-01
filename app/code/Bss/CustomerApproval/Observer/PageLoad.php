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

class PageLoad implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var \Bss\CustomerApproval\Helper\Data
     */
    protected $helper;

    /**
     * PageLoad constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Bss\CustomerApproval\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Bss\CustomerApproval\Helper\Data $helper
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            try {
                $customerId = $this->customerSession->getCustomerId();
                $customer = $this->customerRepositoryInterface->getById($customerId);
                $customerAttr = $customer->getCustomAttribute('activasion_status');
                if ($customerAttr) {
                    $customerValue = (int) $customerAttr->getValue();
                    $disapprove = $this->helper->getDisApproveValue();
                    if ($customerValue == $disapprove) {
                        $this->customerSession->logout();
                    }
                }
            } catch (\Exception $e) {
                // Do nothing
            }
        }
    }
}
