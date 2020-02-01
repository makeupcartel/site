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
namespace Ced\ReferralSystem\Block\Adminhtml\Edit;

/**
 * Class Tabs
 * @package Ced\ReferralSystem\Block\Adminhtml\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs {

	/**
	 *
	 * @return void
	 */
	protected function _construct() {
		parent::_construct ();
		$this->setId ( 'id' );
		$this->setDestElementId ( 'edit_form' );
		$this->setTitle ( __ ( 'Denomination Rule' ) );
		
	}

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function _beforeToHtml()
    {
        $this->addTab ( 'Post', [
                'label' => __ ( 'Discount Denomination Rule' ),
                'content' => $this->getLayout ()
                    ->createBlock ( 'Ced\ReferralSystem\Block\Adminhtml\Edit\Tab\Main' )->toHtml ()
        ] );
		return parent::_beforeToHtml ();
	}
}