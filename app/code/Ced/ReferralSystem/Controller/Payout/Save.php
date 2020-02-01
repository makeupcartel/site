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
namespace Ced\ReferralSystem\Controller\Payout;

/**
 * Class Save
 * @package Ced\ReferralSystem\Controller\Payout
 */
class Save extends \Magento\Framework\App\Action\Action {

    const USED = 1;
    const UNSED = 2;
    const TRANSACTION_TYPE_CREDIT = 1;
    const TRANSACTION_TYPE_DEBIT = 2;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ced\ReferralSystem\Model\ResourceModel\DiscountDenomination\Collection $denominationCollection,
        \Magento\SalesRule\Model\Rule $salesRule,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupCollection,
        \Ced\ReferralSystem\Model\Transaction $transactionModel,
        \Magento\Customer\Model\Customer $customerModel,
        \Ced\ReferralSystem\Model\Coupon $couponModel,
        array $data = []
    ) {
        $this->_date = $date;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->salesRule = $salesRule;
        $this->customerGroupCollection = $customerGroupCollection;
        $this->transactionModel = $transactionModel;
        $this->customerModel = $customerModel;
        $this->couponModel = $couponModel;
        $this->denominationCollection = $denominationCollection;
        parent::__construct ( $context, $data );
    }
    public function execute() {
        
        $data = $this->getRequest()->getPost();
        $amount = $data["discount_coupon"];
        $denomination_range = $this->scopeConfig->getValue('referral/system/referral_reward_denomination_range');
        
        if($amount%$denomination_range!=0){
            $this->messageManager->addErrorMessage(__("Invalid amount"));
            return $this->_redirect('referralsystem/payout/index');
        }
        
        $available = $this->pendingamount();
        if($amount>$available){
            $this->messageManager->addErrorMessage(__("You have insufficient balance"));
            return $this->_redirect('referralsystem/payout/index');
        }

        $customer = $this->customerSession->getCustomer ();
        $customer_Id = $customer->getId ();
        $customer_Email = $customer->getEmail();
        $discountType = "cart_fixed";
        $discountAmount =  $amount; 
        $percoupon = 1;
        $percustomer = 1;
        $email = $customer_Email;
        
        $minpurchase = 0;
        $cart_amount_check_rule = $this->denominationCollection
                                    ->addFieldToFilter('status', 1)
                                    ->addFieldToFilter('discount_amount', $amount)
                                    ->getFirstItem();

        if($cart_amount_check_rule && $cart_amount_check_rule->getId()){
            $minpurchase = $cart_amount_check_rule->getCartAmount();
        }

        $expireDays = $this->scopeConfig ->getValue('referral/system/discount_code_expiration_days');

        $today = date_create($this->_date->date('Y-m-d H:i:s'));
      
        $next = date_format(
            date_add($today, date_interval_create_from_date_string($expireDays." days")),"Y-m-d H:i:s");
       
        $couponlength = 8;
        $promo_name = __('Discount coupon code for '). $email;
        $uniqueId = $this->generatePromoCode($couponlength);
        $rule = $this->salesRule;
        $rule->setName($promo_name);
        $rule->setDescription(__('Generated automatically for the Discount coupon code'));
        $rule->setCouponCode($uniqueId);
        $rule->setFromDate($this->_date->date('Y-m-d H:i:s'));
        $rule->setToDate($next);
        $rule->setUsesPerCoupon($percoupon);
        $rule->setUsesPerCustomer($percustomer);
        $customerGroups = $this->customerGroupCollection;
        $groups = [];
        foreach ($customerGroups as $group){
            $groups[] = $group->getId();
        }
            
        $conditions =
               [
                    '1' =>
                        [
                            'type' => 'Magento\SalesRule\Model\Rule\Condition\Combine',
                            'aggregator' => 'all',
                            'value' => '1',
                            'new_child' => ''
                        ],

                    '1--1' =>
                        [
                            'type' => 'Magento\SalesRule\Model\Rule\Condition\Address',
                            'attribute' => 'base_subtotal',
                            'operator' => '>=',
                            'value' => $minpurchase
                        ]

                ];
        $rule->setData('conditions' , $conditions);
        $rule->setCustomerGroupIds($groups);
        $rule->setIsActive(1);
        $rule->setStopRulesProcessing(1);
        $rule->setIsRss(0);
        $rule->setIsAdvanced(1);
        $rule->setSortOrder(0);          
        $rule->setSimpleAction($discountType);
        $rule->setDiscountAmount($discountAmount);
        $rule->setDiscountQty(0);
        $rule->setDiscountStep(0);
        $rule->setSimpleFreeShipping(0);
        $rule->setApplyToShipping(0);
        $rule->setWebsiteIds([1]);
        $rule->loadPost($rule->getData());
        $rule->setCouponType(2);
        $labels = [];
        $labels[1] = __('Discount Coupon for ').$email;          
        $rule->setStoreLabels($labels);  
        try{
             $rule->save();
        }catch(\Exception $e){
            $e->getMessage();
        }    
       
        $couponModel = $this->couponModel;
        $customer_email = $this->customerModel->load($customer_Id)->getEmail();
        $couponModel->setData('customer_id', $customer_Id);
        $couponModel->setData('email_id', $customer_email);
        $couponModel->setData('coupon_code', $uniqueId);
        $couponModel->setData('status', self::UNSED);
        $couponModel->setData('expiration_date', $next);
        $couponModel->setData('amount', $discountAmount);
        $couponModel->setData('cart_amount', $minpurchase);
        try{
            $couponModel->save();
        }catch(\Exception $e){
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        
        $transaction= $this->transactionModel;
        $transaction->setData('customer_id', $customer_Id);
        $transaction->setData('description', __('Discount Coupon Generated: ').$uniqueId);
        $transaction->setData('earned_amount', $amount);
        $transaction->setData('transaction_type', self::TRANSACTION_TYPE_DEBIT);
        try{
             $transaction->save();
         }catch(\Exception $e){
            $this->messageManager->addErrorMessage(__($e->getMessage()));
         }
         
        $this->messageManager->addSuccessMessage(__("Discount Coupon Generated SuccessFully:". $uniqueId));
        return $this->_redirect('referralsystem/payout/index');        
    }

    private function generatePromoCode($length = null) 
    {
        $rndId = md5(uniqid(rand(),1));
        $rndId = strip_tags(stripslashes($rndId)); 
        $rndId = str_replace([".", "$"],"",$rndId);
        $rndId = strrev(str_replace("/","",$rndId));
        if (!is_null($rndId)){
            return strtoupper(substr($rndId, 0, $length));
        }
        return strtoupper($rndId);
    }

    public function pendingamount(){
        $amount = 0;
        $customer = $this->customerSession->getCustomer ();
        $customer_Id = $customer->getId ();
        $referred_list = $this->transactionModel->getResourceCollection()->addFieldtoFilter ( 'customer_id', [
                'customer_id' => $customer_Id 
        ] );

        foreach($referred_list as $value){
            $amount+=$value['earned_amount'];
        }
        return $amount;
    }
}