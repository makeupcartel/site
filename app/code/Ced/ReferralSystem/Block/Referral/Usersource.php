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

/**
 * Class Usersource
 * @package Ced\ReferralSystem\Block\Referral
 */
class Usersource extends \Magento\Framework\View\Element\Template {

    /**
     * Usersource constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\ReferralSystem\Model\ResourceModel\Refersource\Collection $referSourceCollection
     */
	public function __construct(
		Context $context, 
		\Magento\Customer\Model\Session $customerSession,
        \Ced\ReferralSystem\Model\Refersource $referSourceModel
	) {
        $this->customerSession = $customerSession;
		$this->referralSourceModel = $referSourceModel;
		parent::__construct ( $context );
	}

    /**
     * @param $source
     * @return int|void
     */
	public function getCount($source) {
		$customerId = $this->customerSession->getCustomer()->getId();
		$count = $this->referralSourceModel->getCollection()
                    ->addFieldtoFilter('customer_id', $customerId)
					->addFieldtoFilter('source', $source);
		return count($count);
	}
}