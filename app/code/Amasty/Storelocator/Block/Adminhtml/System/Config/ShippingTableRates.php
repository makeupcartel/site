<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;

class ShippingTableRates extends Field
{
    /**
     * @var Manager
     */
    private $manager;

    public function __construct(
        Manager $manager,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->manager = $manager;
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        if ($this->manager->isEnabled('Amasty_ShippingTableRates')) {
            $element->setValue(__('Installed'));
            $element->setHtmlId('amasty_is_instaled');
            $url = $this->getUrl('adminhtml/system_config/edit', ['section' => 'carriers']);
            $element->setComment(__('Specify Shipping Table Rates settings properly '
                . '<a href="%1" target="_blank">See more details here</a>', $url
            ));
        } else {
            $element->setValue(__('Not Installed'));
            $element->setHtmlId('amasty_not_instaled');
            $element->setComment(__(' Create an unlimited number of flexible shipping methods with individual rates. '
                . 'Use combinations of a destination address, cart weight, order subtotal and price to accurately calculate shipping. '
                . 'See more details <a href="https://amasty.com/shipping-table-rates-for-magento-2.html'
                . '?utm_source=extension&utm_medium=backend&utm_campaign=from_storelocator_to_shippingtablerates_m2" target="_blank">here.</a>'
            ));
        }

        return parent::render($element);
    }
}
