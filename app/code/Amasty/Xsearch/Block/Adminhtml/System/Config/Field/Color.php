<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Adminhtml\System\Config\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Color extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $value = $element->getData('value');
        $html .= '<script type="text/javascript">
            require(["jquery","jquery/colorpicker/js/colorpicker"], function ($) {
                $(document).ready(function () {
                    var input = $("#' . $element->getHtmlId() . '");
                    input.css({"backgroundColor" : "#' . $value . '"});
                    input.ColorPicker({
                        color: "#' . $value . '",
                        onChange: function (hsb, hex, rgb) {
                            input.css({"backgroundColor" : "#" + hex}).val(hex);
                        }
                    });
                });
            });
            </script>';

        return $html;
    }
}
