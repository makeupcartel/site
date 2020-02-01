<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com
/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_ReferralSystem
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com
/)
 * @license      https://cedcommerce.com
/license-agreement.txt
 */
namespace Ced\ReferralSystem\Block\Adminhtml\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface {

    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Data\FormFactory $formFactory,
		array $data = []
	) {
        parent::__construct ( $context, $registry, $formFactory, $data );
	}

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function _prepareForm()
    {
		$data = $this->_coreRegistry->registry( 'discount_form_data' );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Discount Denomination Rule')]);

		if(isset($data)){
			$fieldset->addField ( 'id', 'hidden', [ 
					'name' => 'id' 
			] );
		}
		
		$fieldset->addField ( 'rule_name', 'text', [ 
				'name' => 'rule_name',
				'label' => __ ( 'Rule Name' ),
				'title' => __ ( 'Rule Name' ),
				'required' => true,
				'disabled' => false
		] );

		$fieldset->addField ( 'discount_amount', 'text', [ 
				'name' => 'discount_amount',
				'label' => __ ( 'Discount Amount' ),
				'title' => __ ( 'Discount Amount' ),
				'required' => true,
				'disabled' => false,
				'class' => 'validate-greater-than-zero'
		] );

		$fieldset->addField ( 'cart_amount', 'text', [ 
				'name' => 'cart_amount',
				'label' => __ ( 'Cart Amount' ),
				'title' => __ ( 'Cart Amount' ),
				'required' => true,
				'disabled' => false,
				'class' => 'validate-greater-than-zero'
		] );

		$fieldset->addField ( 'status', 'select', [ 
				'name' => 'status',
				'label' => __ ( 'Status' ),
				'title' => __ ( 'Status' ),
				'required' => true,
				'values' => [
						'-1' => 'Please Select..',
						'0' => 'Disable',
						'1' => 'Enable' 
				]
		] );
		
		if (isset ( $data )) {
			$form->setValues ( $data );
		}
		$this->setForm ( $form );

        return parent::_prepareForm();
	}

    /**
     * {@inheritdoc}
     */
	public function getTabLabel()
    {
		return __ ( 'Discount Denomination' );
	}

    /**
     * {@inheritdoc}
     */
	public function getTabTitle()
    {
		return __ ( 'Discount Denomination' );
	}

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}