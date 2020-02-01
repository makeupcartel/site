<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Block\Adminhtml\Form\Field;

class Combining extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Amasty\ElasticSearch\Model\Source\CombiningType
     */
    private $source;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Amasty\ElasticSearch\Model\Source\CombiningType $source,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->source = $source;
    }

    /**
     * @param $value
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->source->toOptionArray());
        }

        return parent::_toHtml();
    }
}
