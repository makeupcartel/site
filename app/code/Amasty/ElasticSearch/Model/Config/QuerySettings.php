<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Config;

use Amasty\ElasticSearch\Model\Config as ElasticConfig;

class QuerySettings extends \Magento\Framework\App\Config\Value
{
    const ATTRIBUTE = 'attribute';
    const WILDCARD = 'wildcard';
    const SPELLING = 'spelling';
    const COMBINING = 'combining';

    /**
     * @var \Amasty\ElasticSearch\Model\Source\FulltextAttributes
     */
    private $fulltextAttributesSource;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->fulltextAttributesSource = $this->getData('fulltext_attributes');
    }
    /**
     * @inheritdoc
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = $this->makeArrayFieldValue($value);
        $this->setValue($value);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = $this->makeStorableArrayFieldValue($value);
        if ($value) {
            $this->setValue($value);
        } else {
            $this->_dataSaveAllowed = false;
        }
    }

    /**
     * Make value readable by \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param string|array $value
     * @return array
     */
    private function makeArrayFieldValue($value)
    {
        $value = $this->unserializeValue($value);
        $value = $this->encodeArrayFieldValue($value);
        return $value;
    }

    /**
     * Make value ready for store
     *
     * @param string|array $value
     * @return string
     */
    private function makeStorableArrayFieldValue($value)
    {
        $value = $this->decodeArrayFieldValue($value);
        $value = $this->serializeValue($value);
        return $value;
    }

    /**
     * @param array $value
     * @return array
     */
    private function encodeArrayFieldValue(array $value)
    {
        $result = [];
        $attributeCodes = array_keys($this->fulltextAttributesSource->toArray());
        foreach ($value as $attributeCode => $data) {
            if (in_array($attributeCode, $attributeCodes, true)) {
                $result[$attributeCode] = [
                    self::ATTRIBUTE => $attributeCode,
                    self::WILDCARD => $data[self::WILDCARD],
                    self::SPELLING => $data[self::SPELLING],
                    self::COMBINING => $data[self::COMBINING]
                ];
            }
        }

        foreach ($attributeCodes as $code) {
            if (!isset($result[$code])) {
                $result[$code] = [
                    self::ATTRIBUTE => $code,
                    self::WILDCARD => '0',
                    self::SPELLING => '0',
                    self::COMBINING => '0'
                ];
            }
        }

        $sortedResult = [];
        foreach (['name', 'sku', 'description', 'short_description'] as $attrCode) {
            if (isset($result[$attrCode])) {
                $sortedResult[$attrCode] = $result[$attrCode];
                unset($result[$attrCode]);
            }
        }

        $sortedResult = array_merge($sortedResult, $result);
        return $sortedResult;
    }

    /**
     * Create a value from a storable representation
     *
     * @param int|float|string $value
     * @return array
     */
    private function unserializeValue($value)
    {
        $result = [];
        if (is_string($value) && !empty($value)) {
            $result = json_decode($value, true);
        }

        return $result;
    }

    /**
     * Decode value from used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    private function decodeArrayFieldValue(array $value)
    {
        unset($value['__empty']);
        foreach ($value as $attribute => $data) {
            if (!is_array($data) || !array_key_exists(self::COMBINING, $data)) {
                unset($value[$attribute]);
            }
        }

        return $value;
    }

    /**
     * Generate a storable representation of a value
     *
     * @param int|float|string|array $value
     * @return string
     */
    private function serializeValue($value)
    {
        if (is_array($value) && !empty($value )) {
            $result = [];
            foreach ($value as $attributeName => $data) {
                if (!array_key_exists($attributeName, $result)) {
                    $data[self::SPELLING] = isset($data[self::SPELLING]) ? '1' : '0';
                    $data[self::WILDCARD] = isset($data[self::WILDCARD]) ? '1' : '0';
                    $result[$attributeName] = $data;
                }
            }

            return json_encode($result);
        } else {
            return '';
        }
    }

    /**
     * @param string $attributeCode
     * @return array|null
     */
    public function getConfigValue($attributeCode)
    {
        if ($this->getValue() === null) {
            $value = $this->_config->getValue(ElasticConfig::ELASTIC_SEARCH_ENGINE . '/catalog/query_settings');
            $this->setValue($this->makeArrayFieldValue($value));
        }

        $value = $this->getValue();
        $result = isset($value[$attributeCode]) ? $value[$attributeCode] : null;
        unset($result[self::ATTRIBUTE]);
        return $result;
    }
}
