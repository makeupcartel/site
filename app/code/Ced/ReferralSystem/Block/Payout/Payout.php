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
namespace Ced\ReferralSystem\Block\Payout;

use Magento\Framework\View\Element\Template\Context;

class Payout extends \Magento\Framework\View\Element\Template
{
	const REGISTERED = 1;
    const UNREGISTERED = 2;


	public function __construct(
		Context $context, 
		\Magento\Customer\Model\Session $customerSession,
		\Ced\ReferralSystem\Model\ReferList $referListModel,
		\Ced\ReferralSystem\Model\Transaction $transactionModel,
		\Ced\ReferralSystem\Model\DiscountDenomination $denominationModel
	) {
        $this->customerSession = $customerSession;
		$this->referListModel = $referListModel;
		$this->transactionModel = $transactionModel;
		$this->denominationModel = $denominationModel;
		parent::__construct ( $context );
	}

	protected function _prepareLayout() {
		parent::_prepareLayout ();
		$this->pageConfig->getTitle ()->set (__("Payout /Discount Coupons"));
		return $this;
	}

	public function _construct() {
		$customerId = $this->customerSession->getCustomer()->getId();
		$referred_list = $this->referListModel->getCollection()
							->addFieldtoFilter ('customer_id', $customerId)
							->addFieldtoFilter('signup_status', self::REGISTERED);
		$this->setCollection ( $referred_list );
	}

	public function pendingamount(){
		$amount = 0;
        $customerId = $this->customerSession->getCustomer()->getId();
		$referred_list = $this->transactionModel
		                    ->getCollection()
		                    ->addFieldtoFilter ( 'customer_id', $customerId );

		foreach($referred_list as $value){
		    if($value->getTransactionType()==2){
		        $value['earned_amount'] = -$value['earned_amount'];
		    }
			$amount+=$value['earned_amount'];
		}
		return $amount;
	}
	
	public function getDenominationRule($price){
	    $minCartAmount = 0;
	    $rule = $this->denominationModel->getCollection()
        	        ->addFieldToFilter('status', 1)
                    ->addFieldToFilter('discount_amount', $price)
                    ->getFirstItem();
        if($rule && $rule->getId()){
            $minCartAmount = $rule->getCartAmount();
        }

        return $minCartAmount;
	}

	public function getDenominationRange(){
	    return $this->_scopeConfig->getValue('referral/system/referral_reward_denomination_range');
    }
    
    public function getMaxDenominationRange(){
	    return $this->_scopeConfig->getValue('referral/system/referral_reward_max_denomination_range');
    }
}