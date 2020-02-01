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

namespace Ced\ReferralSystem\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Message\ManagerInterface;

Class CheckSignup implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;
    protected $request;
    protected $_scopeConfig;
    const SIGNUP_STATUS_DISABLE = 0;
    const SIGNUP_STATUS_ENABLE = 1;
    const TRANSACTION_TYPE_CREDIT = 1;
    const TRANSACTION_TYPE_DEBIT = 2;
    
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        RequestInterface $request, 
        \Magento\Framework\Stdlib\DateTime\DateTime $date, 
        Encryptor $hashpas,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Ced\ReferralSystem\Model\Transaction $transactionModel,
        \Ced\ReferralSystem\Model\Refersource $refersourceModel,
        \Ced\ReferralSystem\Model\ReferList $referListModel,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\ObjectManagerInterface $objectManager, 
        ManagerInterface $messageManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    )
    {
        $this->request = $request;
        $this->_scopeConfig = $scopeConfig;
        $this->_date = $date;
        $this->encryptor =$hashpas;
        $this->_customerRepository = $customerRepository;
        $this->_customerMapper = $customerMapper;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->transactionModel = $transactionModel;
        $this->referSourceModel = $refersourceModel;
        $this->referListModel = $referListModel;
        $this->customerModel = $customerModel;
        $this->messageManager = $messageManager;
        $this->_objectManager = $objectManager;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try{
            $customer = $observer->getCustomer();
            $customer_id = $customer->getId();
            $referral_id = '';
            $this->saveInvitationCode($customer_id);
            $referral_code = $this->request->getPost('referral_code');
            $referal_source = $this->request->getPost('referal_source')?$this->request->getPost('referal_source'):'email';
            if($referral_code){
                $referral_id = $this->getCustomerIdByReferralCode($referral_code);
            }

            if ($referal_source && $referral_id) {
                try{
                    $referSource = $this->referSourceModel;
                    $referSource->setData('customer_id', $referral_id);
                    $referSource->setData('referred_email',$customer->getEmail());
                    $referSource->setData('source', $referal_source);
                    $referSource->save();
                }catch(\Exception $e){
                    $this->messageManager->addErrorMessage(__($e->getMessage()));
                }
            }
            
           
            $signup_bonus = $this->_scopeConfig->getValue('referral/system/signup_bonus');
            $referral_reward=$this->_scopeConfig->getValue('referral/system/referral_reward');

            if($referral_id){
                $transaction=$this->_objectManager->create('Ced\ReferralSystem\Model\Transaction');
                $transaction->setData('customer_id', $customer_id);
                $transaction->setData('description', "Joining Bonus");
                $transaction->setData('earned_amount', $signup_bonus);
                $transaction->setData('transaction_type', self::TRANSACTION_TYPE_CREDIT);
                $transaction->setData('creation_date', $this->_date->gmtDate());
                $transaction->save();
                $currencySymbol = $this->priceHelper->currency($signup_bonus, true, false);
                $this->messageManager->addSuccessMessage(__('Congratulations! you have received the joining bonus of %1', $currencySymbol));

                $transaction=$this->_objectManager->create('Ced\ReferralSystem\Model\Transaction');
                $transaction->setData('customer_id', $referral_id);
                $transaction->setData('description', "Referral Reward For-".$customer->getEmail());
                $transaction->setData('creation_date', $this->_date->gmtDate());
                $transaction->setData('earned_amount', $referral_reward);
                $transaction->setData('transaction_type', self::TRANSACTION_TYPE_CREDIT);
                $transaction->save();

                $referred_friends_model = $this->referListModel;
                $referred_friends = $referred_friends_model->getCollection()
                    ->addFieldToFilter('referred_email', $customer->getEmail())
                    ->addFieldToFilter('customer_id', $referral_id)->getFirstItem();

                if($referred_friends && $referred_friends->getId()){
                    $referred_friends_model->load($referred_friends->getId());
                    $referred_friends_model->setData('signup_status', self::SIGNUP_STATUS_ENABLE);
                    $referred_friends_model->setData('signup_date', $this->_date->gmtDate());
                    $referred_friends_model->setData('amount', $referral_reward);
                    $referred_friends_model->save();
                }
            }
        }catch(\Exception $e){
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        return $this;
    }

    public function saveInvitationCode($customer_id){
        $customerData = $this->request->getParams();          
        $customerId=$customer_id ;
        $savedCustomerData = $this->_customerRepository->getById($customerId);
        $customerm = $this->_customerDataFactory->create();
        $customerData = array_merge($this->_customerMapper->toFlatArray($savedCustomerData), $customerData);
        $customerData['id'] = $customerId;
        $this->_dataObjectHelper->populateWithArray(
                $customerm,
                $customerData,
                '\Magento\Customer\Api\Data\CustomerInterface'
        );
        try{
            $this->_customerRepository->save($customerm);
        }catch(\Exception $e){
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
    }

    public function getCustomerIdByReferralCode($referral_code){
        $customer = $this->customerModel->getCollection()->addAttributeToFilter('invitation_code', $referral_code)
            ->getFirstItem();
        $customerId = $customer->getId();
        return $customerId;
    }
}
