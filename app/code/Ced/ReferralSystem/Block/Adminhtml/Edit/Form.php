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

class Form extends \Magento\Backend\Block\Widget\Form\Generic {

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function _prepareForm() {
		
		/** @var \Magento\Framework\Data\Form $form */
		$form = $this->_formFactory->create ( [ 
				'data' => [ 
						'id' => 'edit_form',
						'action' => $this->getData ( 'action' ),
						'method' => 'post',
						'enctype' => 'multipart/form-data' 
				] 
		] );
		$form->setUseContainer ( true );
		$this->setForm ( $form );
		return parent::_prepareForm ();
	}
}