<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Rule;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class ReindexNowButton
 * @package Amasty\Reports\Block\Adminhtml\Rule
 */
class ReindexNowButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $ruleId = $this->getRuleId();
        if ($ruleId) {
            $data = [
                'label'      => __('Reindex Now'),
                'class'      => 'delete',
                'on_click'   => 'setLocation(\'' .
                    $this->getUrlBuilder()->getUrl('*/*/reindex', ['id' => $ruleId]) .
                    '\')',
                'sort_order' => 30,
            ];
        }

        return $data;
    }
}
