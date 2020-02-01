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
namespace Ced\ReferralSystem\Block\Adminhtml;

class Discount extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Discount constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
	public function __construct(
		\Magento\Backend\Block\Widget\Context $context, 
		array $data = []
	) {
		parent::__construct ( $context, $data );
	}

	protected function _construct() {
		$this->_objectId = 'id';
        $this->_controller = 'adminhtml';
		$this->_blockGroup = 'Ced_ReferralSystem';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Rule'));
        $this->buttonList->update('delete', 'label', __('Delete Rule'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
	}

    /**
     * Retrieve the save and continue edit Url.
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/discount/save',
            ['_current' => true, 'back' => 'edit', 'tab' => '{{tab_id}}']
        );
    }

    public function getBackUrl() {
        return $this->getUrl ( '*/discount/denomination' );
    }
}