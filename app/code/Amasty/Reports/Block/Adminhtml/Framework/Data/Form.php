<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Framework\Data;

use Magento\Framework\Profiler;

/**
 * Class Form
 */
class Form extends \Magento\Framework\Data\Form
{
    /**
     * @return string
     */
    public function toHtml()
    {
        Profiler::start('form/toHtml');
        $html = '';
        $useContainer = $this->getUseContainer();
        //@codingStandardsIgnoreStart
        if ($useContainer) {
            $html .= '<form ' . $this->serialize($this->getHtmlAttributes()) . '>';
            if (strtolower($this->getData('method')) == 'post') {
                $html .= '<div><input name="form_key" type="hidden" value="'
                    . $this->formKey->getFormKey() . '" /></div>';
            }
        }

        foreach ($this->getElements() as $element) {
            $elementHtml = $element->toHtml();
            if ($elementHtml) {
                $elementHtml = sprintf(
                    '<div class="amreports-field %s">%s</div>',
                    $element->getWrapperClass(),
                    $elementHtml
                );
            }
            $html .= $elementHtml;
        }
        //@codingStandardsIgnoreEnd
        if ($useContainer) {
            $html .= '</form>';
        }
        Profiler::stop('form/toHtml');
        return $html;
    }
}
