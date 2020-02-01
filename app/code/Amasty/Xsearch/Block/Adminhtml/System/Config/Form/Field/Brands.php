<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Adminhtml\System\Config\Form\Field;

class Brands extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->getModuleManager() && $this->getModuleManager()->isEnabled('Amasty_ShopbyBrand')) {
            $html = parent::render($element);
        } else {
            $link = 'https://amasty.com/improved-layered-navigation-for-magento-2.html?utm_source=extension&utm_medium=backend&utm_campaign=Amasty_Xsearch-to-iln_m2';
            $linkBrand = 'https://amasty.com/shop-by-brand-for-magento-2.html?utm_source=extension&utm_medium=backend&utm_campaign=Amasty_Xsearch-to-brand_m2';
            $html = '<tr id="row_brand_amasty_not_instaled"><td class="label">
                <label for="brand_amasty_not_instaled">
                    <span>' . __('Status') . '</span>
                </label></td><td class="value"><div class="control-value">' . __('Not Installed')
                . '</div><p class="note"><span>'
                . __('For <a target=\'_blank\' href=\'%1\'>Improved Layered Navigation</a> and <a target=\'_blank\' href=\'%2\'>Shop by Brand</a>.', $link, $linkBrand)
                . '</span></p></td><td class=""></td></tr>';
        }

        return $html;
    }
}
