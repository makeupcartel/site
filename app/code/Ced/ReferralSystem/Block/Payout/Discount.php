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

class Discount extends \Magento\Framework\View\Element\Template
{
	const USED = 1;
    const UNSED = 2;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Discount constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Ced\ReferralSystem\Model\ResourceModel\Coupon\Collection $referralCoupon
     * @param \Magento\SalesRule\Model\Coupon $salesCoupon
     */
	public function __construct(
		Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\ReferralSystem\Model\Coupon $referralCouponModel,
        \Magento\SalesRule\Model\Coupon $salesCoupon
	) {
        $this->customerSession = $customerSession;
		$this->referralCouponModel = $referralCouponModel;
		$this->salesCoupon = $salesCoupon;
		parent::__construct ( $context );
	}

	public function _construct() {
		$customer_Id = $this->customerSession->getCustomer()->getId ();
		$coupons = $this->referralCouponModel->getCollection()->addFieldtoFilter ( 'customer_id', $customer_Id );
		$this->setCollection ( $coupons );
	}
	
	public function getDiscountCouponStatus($coupon_code){
	    $coupon = $this->salesCoupon->load($coupon_code, 'code');
        if($coupon->getId()) {
            $timesUsed = $coupon->getTimesUsed();
            if($timesUsed>0){
                return self::USED;
            }else{
                return self::UNSED;
            }
        }
	}

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

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