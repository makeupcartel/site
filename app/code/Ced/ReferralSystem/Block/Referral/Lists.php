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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\ReferralSystem\Block\Referral;
use Magento\Framework\View\Element\Template\Context;

class Lists extends \Magento\Framework\View\Element\Template {

	const REGISTERED = 1;
    const UNREGISTERED = 2;

	public function __construct(
		Context $context,
		\Magento\Customer\Model\Session $customerSession,
        \Ced\ReferralSystem\Model\ReferList $referListModel,
        \Magento\Customer\Model\Customer $customerModel
	) {
        $this->customerSession = $customerSession;
        $this->referListModel = $referListModel;
        $this->customerModel = $customerModel;
		parent::__construct ( $context );
	}

	public function _construct()
    {
		$customerId = $this->customerSession->getCustomer()->getId ();
		$referred_list = $this->referListModel->getCollection()->addFieldtoFilter ( 'customer_id', $customerId );
		$this->setCollection ( $referred_list );
	}
	
	protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Referral Report'));
    
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'ced.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15))->setShowPerPage(true)->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }
	
	public function getPagerHtml()
    {
		return $this->getChildHtml ( 'pager' );
	}

	public function pendingamount()
    {
		$amount = 0;
		$referred_list = $this->getCollection();
		foreach($referred_list as $value){
			$amount+=$value['amount'];
		}
		return $amount;
	}

	public function pendingreferral()
    {
		$customerId = $this->customerSession->getCustomer()->getId ();
		$pendingreferral = $this->referListModel->getCollection()
								->addFieldtoFilter ( 'customer_id', $customerId )
								->addFieldtoFilter('signup_status', self::UNREGISTERED)
								->getData();
		return sizeof($pendingreferral);
	}

	public function registeredreferral(){
        $total = 0;
        $customerId = $this->customerSession->getCustomer()->getId ();
		$customer = $this->customerModel->load($customerId);
        if($customer && $customer->getId()){
            $invitationCode = $customer->getInvitationCode();
            if($invitationCode){
                $allCustomers = $this->customerModel->getCollection()->addFieldtoFilter('referral_code', $invitationCode)
                    ->getAllIds();
                if(!empty($allCustomers)){
                    $total = count($allCustomers);
                }
            }
        }
        return $total;
	}
}