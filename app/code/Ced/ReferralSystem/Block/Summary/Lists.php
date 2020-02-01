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
namespace Ced\ReferralSystem\Block\Summary;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Lists
 * @package Ced\ReferralSystem\Block\Summary
 */
class Lists extends \Magento\Framework\View\Element\Template
{

    /**
     * Lists constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\ReferralSystem\Model\ResourceModel\Transaction\Collection $transactionCollection
     */
	public function __construct(
		Context $context, 
		\Magento\Customer\Model\Session $customerSession,
		\Ced\ReferralSystem\Model\Transaction $transactionModel
	) {
        $this->customerSession = $customerSession;
		$this->transactionModel = $transactionModel;
		parent::__construct ( $context );
	}

	public function _construct()
    {
		$customerId = $this->customerSession->getCustomer()->getId ();
		$transactions = $this->transactionModel->getCollection()
							->addFieldtoFilter ( 'customer_id', $customerId );
		$this->setCollection ( $transactions );
	}
	
	protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Transaction Summary'));
    
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
        return $this->getChildHtml('pager');
    }
}