<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Rule;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveAndContinueButton
 * @package Amasty\Reports\Block\Adminhtml\Rule
 */
class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label'      => __('Save and Continue Edit'),
            'class'      => 'save',
            'on_click'   => '',
            'sort_order' => 90,
        ];
    }
}
