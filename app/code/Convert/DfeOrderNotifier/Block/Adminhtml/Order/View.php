<?php

namespace Convert\DfeOrderNotifier\Block\Adminhtml\Order;

/**
 * Class View
 *
 * @package Convert\DfeOrderNotifier\Block\Adminhtml\Order
 */
class View extends \Magento\Sales\Block\Adminhtml\Order\View
{
    /**
     * add send to net suite button
     */
    protected function _construct()
    {
        parent::_construct();
        $message = __('Are you sure you want to send an order to Netsuite?');
        $this->addButton(
            'send_netsuite',
            [
                'label' => __('Send to Netsuite'),
                'class' => 'send-email',
                'onclick' => "confirmSetLocation('{$message}', '{$this->getSendNetsuiteUrl()}')"
            ]
        );
    }

    /**
     * Email URL getter
     *
     * @return string
     */
    private function getSendNetsuiteUrl()
    {
        return $this->getUrl('csales/*/send');
    }
}