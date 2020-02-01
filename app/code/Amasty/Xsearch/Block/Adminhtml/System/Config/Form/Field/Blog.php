<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Adminhtml\System\Config\Form\Field;

class Blog extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($this->getModuleManager() && $this->getModuleManager()->isEnabled('Amasty_Blog')) {
            $html = parent::render($element);
        } else {
            $linkBlog = 'https://amasty.com/blog-pro-for-magento-2.html?utm_source=extension&utm_medium=backend&utm_campaign=Amasty_Xsearch-to-blog_m2';
            $html = '<tr id="row_brand_amasty_not_instaled"><td class="label">
                <label for="brand_amasty_not_instaled">
                    <span>' . __('Status') . '</span>
                </label></td><td class="value"><div class="control-value">' . __('Not Installed')
                . '</div><p class="note"><span>'
                . __('Allows to search by blog pages created with Amasty <a target=\'_blank\' href=\'%1\'>Blog</a> extension.', $linkBlog)
                . '</span></p></td><td class=""></td></tr>';
        }

        return $html;
    }
}
