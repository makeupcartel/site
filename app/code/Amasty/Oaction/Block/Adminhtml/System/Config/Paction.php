<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;

class Paction extends Field
{
    /**
     * @var Magento\Framework\Module\Manager
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
        if ($this->manager->isEnabled('Amasty_Paction')) {
            $element->setValue(__('Installed'));
            $element->setHtmlId('amasty_is_instaled');
            $url = $this->getUrl('adminhtml/system_config/edit', ['section' => 'amasty_paction']);
            $element->setComment(__('Specify Mass Product Actions settings properly. See more details '
                . '<a href="%1" target="_blank">here</a>',
                $url
            ));
        } else {
            $element->setValue(__('Not Installed'));
            $element->setHtmlId('amasty_not_instaled');
            $element->setComment(__('Increase the efficiency of the catalogue management.'
                    . ' Change prices, manage attribute sets and categories, spread custom options and images, and related products.'
                    . ' Apply bulk modifications to multiple products and tweak the actions menu to display only required actions.'
                    . ' See more details <a target="_blank" href="https://amasty.com/mass-product-actions-for-magento-2.html?utm_source=extension&amp;utm_medium=backend&amp;utm_campaign=m2_order_actions_to_product_actions">here</a>')
            );
        }

        return parent::render($element);
    }
}
