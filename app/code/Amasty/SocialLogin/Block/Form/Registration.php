<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Block\Form;

class Registration extends \Magento\Customer\Block\Form\Register
{
    /**
     * @return Registration|\Magento\Customer\Block\Form\Register|\Magento\Framework\View\Element\AbstractBlock
     */
    public function _prepareLayout()
    {
        $parent = $this->getParentBlock();
        return $parent ? $parent->_prepareLayout() : $this;
    }
}
