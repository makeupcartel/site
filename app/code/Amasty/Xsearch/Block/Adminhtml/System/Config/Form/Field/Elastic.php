<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Adminhtml\System\Config\Form\Field;

class Elastic extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->getModuleManager() && $this->getModuleManager()->isEnabled('Amasty_ElasticSearch')) {
            $element->setValue(__("Installed"));
            $element->setHtmlId('elastic_search_amasty_is_instaled');
            $element->setComment('');
        } else {
            $element->setValue(__('Not Installed'));
            $element->setHtmlId('elastic_search_amasty_not_instaled');
        }

        return parent::render($element);
    }
}
