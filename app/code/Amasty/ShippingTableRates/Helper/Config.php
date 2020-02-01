<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingTableRates
 */


namespace Amasty\ShippingTableRates\Helper;

use Magento\Store\Model\ScopeInterface;
use Zend\Validator\Regex as RegexValidator;

class Config extends Data
{
    const VOLUMETRIC_WEIGHT_ATTRIBUTE_TYPE = 'volumetric_weight_attribute';
    const VOLUMETRIC_ATTRIBUTE_TYPE = 'volumetric_attribute';
    const VOLUMETRIC_DIMENSIONS_ATTRIBUTE = 'volumetric_dimmensions_attribute';
    const VOLUMETRIC_SEPARATE_DIMENSION_ATTRIBUTE= 'volumetric_separate_dimmension_attribute';

    const XML_PATH_VOLUMETRIC_WEIGHT = 'carriers/amstrates/volumetric_weight';
    const XML_PATH_VOLUMETRIC_WEIGHT_ATTRIBUTE = 'carriers/amstrates/volumetric_weight_attribute';
    const XML_PATH_VOLUMETRIC_ATTRIBUTE = 'carriers/amstrates/volumetric_attribute';
    const XML_PATH_DIMENSIONS_ATTRIBUTE = 'carriers/amstrates/dimensions_attribute';
    const XML_PATH_FIRST_SEP_DIMENSION_ATTRIBUTE = 'carriers/amstrates/first_sep_dimension_attribute';
    const XML_PATH_SECOND_SEP_DIMENSION_ATTRIBUTE = 'carriers/amstrates/second_sep_dimension_attribute';
    const XML_PATH_THIRD_SEP_DIMENSION_ATTRIBUTE = 'carriers/amstrates/third_sep_dimension_attribute';
    const XML_PATH_SHIPPING_VACTOR = 'carriers/amstrates/shipping_factor';

    const PATTERN_VALID_VOLUME_DIMENSION = '/^((?:\d+?)(?:[.,](?:\d+?)(?=[^\d.,\s]))?)(?:[^\d.,\s])((?:\d+?)(?:[.,](?:\d+?)(?=[^\d.,\s]))?)(?:[^\d.,\s])((?:\d+?)(?:[.,](?:\d+?))?)$/';

    /**
     * The method gets value of the option 'Volumetric weight'
     *
     * @return mixed
     */
    public function getTypeVolumetricWeight()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_VOLUMETRIC_WEIGHT);
    }

    /**
     * The method gets value of 'Volumetric Weight Attribute'
     *
     * @return mixed
     */
    public function getVolumetricWeightAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_VOLUMETRIC_WEIGHT_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * The method gets value of the option 'Volumetric attribute'
     *
     * @return mixed
     */
    public function getVolumetricAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_VOLUMETRIC_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * The method gets value of the option 'Dimensions attribute'
     *
     * @return mixed
     */
    public function getDimensionsAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_DIMENSIONS_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * The method gets value of the option 'Attribute 1'
     *
     * @return mixed
     */
    public function getFirstSeparateDimensionAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_FIRST_SEP_DIMENSION_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * The method gets value of the option 'Attribute 2'
     *
     * @return mixed
     */
    public function getSecondSeparateDimensionAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SECOND_SEP_DIMENSION_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * The method gets value of the option 'Attribute 3'
     *
     * @return mixed
     */
    public function getThirdSeparateDimensionAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_THIRD_SEP_DIMENSION_ATTRIBUTE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * The method gets value of shipping factor
     *
     * @return mixed
     */
    public function getShippingFactor()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SHIPPING_VACTOR, ScopeInterface::SCOPE_STORE);
    }

    /**
     * The method gets value of attribute code
     *
     * @return array
     */
    public function getSelectedWeightAttributeCode()
    {
        $selectedWeightAttributeCodes = [];
        $typeVolumetricWeight = $this->getTypeVolumetricWeight();
        switch ($typeVolumetricWeight) {
            case self::VOLUMETRIC_WEIGHT_ATTRIBUTE_TYPE:
                $selectedWeightAttributeCodes[] = $this->getVolumetricWeightAttribute();
                break;
            case self::VOLUMETRIC_ATTRIBUTE_TYPE:
                $selectedWeightAttributeCodes[] = $this->getVolumetricAttribute();
                break;
            case self::VOLUMETRIC_DIMENSIONS_ATTRIBUTE:
                $selectedWeightAttributeCodes[] = $this->getDimensionsAttribute();
                break;
            case self::VOLUMETRIC_SEPARATE_DIMENSION_ATTRIBUTE:
                $selectedWeightAttributeCodes[] = $this->getFirstSeparateDimensionAttribute();
                $selectedWeightAttributeCodes[] = $this->getSecondSeparateDimensionAttribute();
                $selectedWeightAttributeCodes[] = $this->getThirdSeparateDimensionAttribute();
                break;
        }

        return $selectedWeightAttributeCodes;
    }

    /**
     * The method  calculates volumetric weight
     *
     * @param mixed $volumeWeight
     *
     * @return float|mixed
     */
    public function calculateVolumetricWeightWithShippingFactor($volumeWeight = 0)
    {
        $volumetricWeight = $volumeWeight;
        $typeVolumetricWeight = $this->getTypeVolumetricWeight();
        $shippingFactor = (int)$this->getShippingFactor();
        if ($typeVolumetricWeight == self::VOLUMETRIC_ATTRIBUTE_TYPE
            || $typeVolumetricWeight == self::VOLUMETRIC_SEPARATE_DIMENSION_ATTRIBUTE
        ) {
            $volumetricWeight = $shippingFactor ? $volumeWeight / $shippingFactor : 0;
        } else if ($typeVolumetricWeight == self::VOLUMETRIC_DIMENSIONS_ATTRIBUTE) {
            $volumeByDimensions = $this->calculateVolumeByDimensionsAttribtue($volumeWeight);
            $volumetricWeight = $shippingFactor ? $volumeByDimensions / $shippingFactor : 0;
        }

        return (float)$volumetricWeight;
    }

    /**
     * The method calculates volume by dimension
     *
     * @param string $dimensions
     *
     * @return float
     */
    public function calculateVolumeByDimensionsAttribtue($dimensions = '')
    {
        $volume = 0;
        if ($this->isVolumeDimensions($dimensions)) {
            $dimensionNumbers = [];
            preg_match(self::PATTERN_VALID_VOLUME_DIMENSION, $dimensions, $dimensionNumbers);
            array_shift($dimensionNumbers);
            if (!empty($dimensionNumbers)) {
                $volume = 1;
                foreach ($dimensionNumbers as $number) {
                    $number = str_replace(',', '.', $number);
                    $volume *= (float)$number;
                }
            }
        }

        return (float)$volume;
    }

    /**
     * The method checks format of volume dimensions
     *
     * @param string $dimensions
     *
     * @return bool
     */
    private function isVolumeDimensions($dimensions = '')
    {
        $volumeDimensionsValidator = new RegexValidator(self::PATTERN_VALID_VOLUME_DIMENSION);

        return $volumeDimensionsValidator->isValid($dimensions);
    }
}
