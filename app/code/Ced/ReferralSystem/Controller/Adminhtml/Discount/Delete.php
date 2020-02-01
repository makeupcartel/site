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
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;

/**
 * Class Delete
 * @package Ced\ReferralSystem\Controller\Adminhtml\Discount
 */
class Delete extends \Magento\Backend\App\Action
{
    public function __construct(
        Context $context,
        \Ced\ReferralSystem\Model\DiscountDenomination $denominationRule
    ){
        $this->denominationRules = $denominationRule;
        parent::__construct($context);
    }

    public function execute()
    {
        $ruleId = $this->getRequest()->getParam('id');
        $denominationRule = $this->denominationRules->load($ruleId);
        $denominationRule->delete();
        $this->messageManager->addSuccessMessage(__('Denomination rule has been deleted successfully.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('referralsystem/discount/denomination');
    }
}