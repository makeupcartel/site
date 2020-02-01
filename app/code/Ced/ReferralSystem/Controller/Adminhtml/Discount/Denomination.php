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
namespace Ced\ReferralSystem\Controller\Adminhtml\Discount;

/**
 * Class Denomination
 * @package Ced\ReferralSystem\Controller\Adminhtml\Discount
 */
class Denomination extends \Magento\Backend\App\Action {

    /**
     * Denomination constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
	public function __construct(
		\Magento\Backend\App\Action\Context $context, 
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	) {
		parent::__construct ( $context );
		$this->resultPageFactory = $resultPageFactory;
	}

	public function execute() {
		$resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_ReferralSystem::referral');
		$resultPage->getConfig()->getTitle()->prepend( __ ( 'Denomination Rules'));
        return $resultPage;
    }
}