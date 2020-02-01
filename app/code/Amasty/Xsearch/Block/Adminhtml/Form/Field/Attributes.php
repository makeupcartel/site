<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Adminhtml\Form\Field;

class Attributes extends \Magento\Framework\View\Element\Html\Select
{
    const EXCLUDED_ATTRIBUTES = ['category_ids', 'visibility'];

    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $xSearchHelper;

    /**
     * Attributes constructor.
     * @param \Amasty\Xsearch\Helper\Data $xSearchHelper
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Amasty\Xsearch\Helper\Data $xSearchHelper,
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->xSearchHelper = $xSearchHelper;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $productAttributes = $this->xSearchHelper->getProductAttributes();
        foreach ($productAttributes as $attribute) {
            if (!in_array($attribute->getAttributeCode(), self::EXCLUDED_ATTRIBUTES)) {
                $this->addOption($attribute->getAttributeCode(), $this->escapeQuote($attribute->getFrontendLabel()));
            }
        }

        return parent::_toHtml();
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
