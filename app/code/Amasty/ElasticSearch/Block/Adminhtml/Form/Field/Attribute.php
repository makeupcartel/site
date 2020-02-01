<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Block\Adminhtml\Form\Field;

/**
 * @method array|null getFulltextAttributes()
 * @method Attribute setFulltextAttributes(array $attributes)
 */
class Attribute extends \Magento\Framework\View\Element\Text
{
    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        $this->setText('');
        foreach (array_keys($this->getFulltextAttributes()) as $attributeCode) {
            $this->addText('<%= option_extra_attrs.option_' . self::calcOptionHash($attributeCode) . ' %>');
        }

        return parent::_toHtml();
    }

    /**
     * @param string $optionValue
     * @return string
     */
    public function calcOptionHash($optionValue)
    {
        return sprintf('%u', crc32($this->getData('name') . $this->getData('id') . $optionValue));
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setData('name', $value);
    }
}
