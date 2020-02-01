<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Adminhtml\Form\Field;

class Weight extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var Weight
     */
    private $weightSource;

    /**
     * Weight constructor.
     * @param \Magento\CatalogSearch\Model\Source\Weight $weightSource
     * @param \Magento\Framework\View\Element\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\CatalogSearch\Model\Source\Weight $weightSource,
        \Magento\Framework\View\Element\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->weightSource = $weightSource;
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $this->setOptions($this->weightSource->getOptions());
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
