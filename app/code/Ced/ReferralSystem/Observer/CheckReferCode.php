<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_ReferralSystem
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
 
namespace Ced\ReferralSystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

class CheckReferCode implements ObserverInterface
{
    protected $_customerCollectionFactory;
    public function __construct(
        \Magento\Customer\Model\Customer $customerModel,
        RequestInterface $request,
        ManagerInterface $messageManager
    ) {
        $this->_customerModel = $customerModel;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }


    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $referral_code = $this->request->getPost('referral_code');
        
        $customer = $observer->getCustomer();
        if(!$customer->getId() && trim($referral_code)!=''){
            $customerCollection= $this->_customerModel->getCollection()
                                    ->addAttribuTeToFilter('invitation_code', $referral_code)
                                    ->getFirstItem();
            if (!$customerCollection->getId()) {
                throw new NotFoundException(
                    __('Invalid referral code. Please, try again.')
                );
            }
        }
    }
}