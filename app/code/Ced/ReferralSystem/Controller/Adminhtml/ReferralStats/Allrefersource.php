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
namespace Ced\ReferralSystem\Controller\Adminhtml\ReferralStats;

/**
 * Class Allrefersource
 * @package Ced\ReferralSystem\Controller\Adminhtml\ReferralStats
 */
class Allrefersource extends \Magento\Backend\App\Action {

	protected $resultPageFactory;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Ced\ReferralSystem\Model\Refersource $referralSourceModel,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
	) {
		parent::__construct ( $context );
		$this->referralSourceModel = $referralSourceModel;
        $this->resultRawFactory = $resultRawFactory;
	}

	public function execute() {
		$fb = $this->getCount('facebook');
		$google = $this->getCount('google');
		$twitter = $this->getCount('twitter');
		$email = $this->getCount('email');

		$html = '<div class="social-referrel">
					<ul>
						<li class="google">
							<label>Google : </label>
							<span class="referral-counter">'.$google.'</span>
						</li>
						<li class="fb">
							<label>Facebook : </label>
							<span class="referral-counter">'.$fb.'</span>
						</li>
						<li class="twitter">
							<label>Twitter : </label>
							<span class="referral-counter">'.$twitter.'</span>
						</li>
						<li class="email">
							<label>Emails : </label>
							<span class="referral-counter">'.$email.'</span>
						</li>
					</ul>
				</div>';

        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($html));
        return $response;
    }

    public function getCount($source) {
		$count = $this->referralSourceModel->getCollection()
					->addFieldtoFilter('source',$source);
		return count($count);
	}
}