<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Adminhtml\System\Config;

use Magento\Store\Model\ScopeInterface;

class CssInclude extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Amasty\Base\Helper\CssChecker
     */
    private $cssChecker;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\Base\Helper\CssChecker $cssChecker,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cssChecker = $cssChecker;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $corruptedWebsites = $this->cssChecker->getCorruptedWebsites();
        $configValue = $this->_scopeConfig->isSetFlag(
            'amasty_xsearch/amasty_information/css_include',
            ScopeInterface::SCOPE_STORE
        );

        //if setting enabled - customer should have ability to disable it
        if ($corruptedWebsites || $configValue) {
            return parent::render($element);
        }
        return '';
    }
}