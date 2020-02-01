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
use Ced\ReferralSystem\Model\ResourceModel\DiscountDenomination\Collection;

/**
 * Class Save
 * @package Ced\ReferralSystem\Controller\Adminhtml\Discount
 */
class Save extends \Magento\Backend\App\Action {

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Ced\ReferralSystem\Model\DiscountDenomination $denominationRule
     * @param Collection $denominationCollection
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Ced\ReferralSystem\Model\DiscountDenomination $denominationRule,
        \Ced\ReferralSystem\Model\ResourceModel\DiscountDenomination\Collection $denominationCollection,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	) {
		$this->resultPageFactory = $resultPageFactory;
		$this->denominationRules = $denominationRule;
		$this->denominationCollection = $denominationCollection;
		parent::__construct ( $context );
	}


	public function execute() {
	    
		$data = $this->getRequest ()->getPostValue ();
        $id = $this->getRequest ()->getParam ( 'id' );
		$resultRedirect = $this->resultRedirectFactory->create ();
		if ($data) {
            $denominationModel = $this->denominationRules;
			if ($id) {
			    $denominationModel = $denominationModel->load ( $id );
			}else{
			    $allRules = $this->denominationCollection
                    ->addFieldToFilter('discount_amount', $data ['discount_amount'])
                    ->getFirstItem();

                if($allRules && $allRules->getId()){
                    $this->messageManager->addErrorMessage (__('Rule already exists for Discount amount %1',
                        $data ['discount_amount']));
                    return $resultRedirect->setPath ( '*/discount/edit', [ 
        					'id' => $this->getRequest ()->getParam ( 'id' ) 
        			] );
                }
			}

            $denominationModel->setData ( 'rule_name', $data["rule_name"] );
            $denominationModel->setData ( 'discount_amount', $data ['discount_amount'] );
            $denominationModel->setData ( 'cart_amount', $data ['cart_amount'] );
            $denominationModel->setData ( 'status', $data ['status'] );
			
			try {
                $denominationModel->save ();
				$this->messageManager->addSuccessMessage ( __ ( 'Discount Rule successfully saved.' ) );
				if ($this->getRequest ()->getParam ( 'back' )) {
					return $resultRedirect->setPath ( '*/discount/edit', [ 
							'id' => $denominationModel->getId (),
							'_current' => true 
					] );
				}
				return $resultRedirect->setPath ( '*/discount/denomination' );
			} catch ( \Exception $e ) {
				$this->messageManager->addErrorMessage( __ ( 'Something went wrong while saving the data.' ) );
			}
			
			$this->_getSession ()->setFormData ( $data );
			return $resultRedirect->setPath ( '*/discount/denomination', [ 
					'id' => $this->getRequest ()->getParam ( 'id' ) 
			] );
		}
		return $resultRedirect->setPath ( '*/discount/denomination' );
	}
}