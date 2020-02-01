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
namespace Ced\ReferralSystem\Helper;
 
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_inlineTranslation = $inlineTranslation;
        $this->_customerSession = $customerSession;
        $this->date = $date;
        $this->_transportBuilder = $transportBuilder;
        $this->customerModel = $customerModel;
        $this->_storeManager = $storeManager;
    }

    public function isEnable() {
		$value = $this->scopeConfig->getValue ( 'advanced/modules_disable_output/Ced_ReferralSystem' );
		return $value;
	}

	public function sendInvitationEmail($emails, $message, $subject, $referral_url)
    {   
        $support = $this->scopeConfig->getValue('referral/system/support_email');

        $customerId = $this->_customerSession->getCustomer()->getId();
    	$modeldata = $this->customerModel->load($customerId);

    	$emailvariables['name'] = $modeldata->getFirstname();

    	$emailvariables['storename'] = $this->scopeConfig->getValue('general/store_information/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $emailvariables['subject'] = $subject;
        $emailvariables['message'] = $message;
        $emailvariables['referral_url'] = $referral_url;
        
    	$this->_template = 'invitation_referal_email';
    	$this->_inlineTranslation->suspend();
        $senderInfo = [
            'name' => 'SUPPORT',
            'email' => $support,
        ];
        
        $sent = 0;


        try {
            foreach ($emails as $email) {
            	$this->_transportBuilder->setTemplateIdentifier($this->_template)
                	->setTemplateOptions(
                			[
                			'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                			'store' => $this->_storeManager->getStore()->getId(),
                			]
                	)
            	->setTemplateVars($emailvariables)
            	->setFrom($senderInfo)
            	->addTo($email, 'Referral');

        		$transport = $this->_transportBuilder->getTransport();
        		$transport->sendMessage();
                $sent++;
            }
        }catch (\Exception $e) {
            return false;
        }
    	$this->_inlineTranslation->resume();
        return $sent;
    }

    public function generatePromoCode() 
    {
        $length = 6;
        $rndId = md5(uniqid(rand(),1));
        $rndId = strip_tags(stripslashes($rndId)); 
        $rndId = str_replace([".", "$"],"",$rndId);
        $rndId = strrev(str_replace("/","",$rndId));
        if (!is_null($rndId)){
            return strtoupper(substr($rndId, 0, $length));
        } 
        return strtoupper($rndId);
    }
}
