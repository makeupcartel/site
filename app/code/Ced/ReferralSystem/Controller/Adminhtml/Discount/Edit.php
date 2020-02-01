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
 * Class Edit
 * @package Ced\ReferralSystem\Controller\Adminhtml\Discount
 */
class Edit extends \Magento\Backend\App\Action {

	protected $_coreRegistry = null;
	protected $resultPageFactory;

	public function __construct(
		\Magento\Backend\App\Action\Context $context, 
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Ced\ReferralSystem\Model\DiscountDenomination $denominationRule,
		\Magento\Framework\Registry $registry
	) {
		$this->resultPageFactory = $resultPageFactory;
		$this->_coreRegistry = $registry;
		$this->denominationRules = $denominationRule;
		parent::__construct ( $context );
	}

	protected function _initAction() {
		$resultPage = $this->resultPageFactory->create ();
		return $resultPage;
	}
	
	public function execute() {
		$id = $this->getRequest ()->getParam ( "id" );
        $denominationModel = null;
		if ($id) {
			$denominationModel = $this->denominationRules->load( $id );
            $this->_coreRegistry->register ( 'discount_form_data', $denominationModel );
		}

		$resultPage = $this->_initAction ();
        $resultPage->setActiveMenu('Ced_ReferralSystem::referral');
		$resultPage->addBreadcrumb ( $id ? __ ( 'Edit Discount Denomination Rule' ) :
            __ ( 'New Discount Denomination Rule' ), $id ?
            __ ( 'Edit Discount Denomination Rule' ) : __ ( 'New Rule' ) );
		$resultPage->getConfig ()->getTitle ()->prepend ( __ ( 'Rules' ) );
		$resultPage->getConfig ()->getTitle ()->prepend ( $denominationModel ?
            $denominationModel->getTitle () : __ ( 'Add Discount Denomination Rule' ) );
		return $resultPage;
	}
}