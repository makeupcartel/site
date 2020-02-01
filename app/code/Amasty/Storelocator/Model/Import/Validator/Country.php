<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Import\Validator;

use \Amasty\Storelocator\Model\Import\Location as Location;

class Country extends AbstractImportValidator implements RowValidatorInterface
{

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    private $countryHelper;

    public function __construct(
        \Magento\Directory\Model\Config\Source\Country $countryHelper,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->countryFactory = $countryFactory;
        $this->countryHelper = $countryHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function init($context)
    {
        return parent::init($context);
    }

    /**
     * Validate value
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        $valid = true;
        if (isset($value[Location::COL_COUNTRY]) && !empty($value[Location::COL_COUNTRY])) {
            $valid *= $this->isCountryValid($value[Location::COL_COUNTRY]);
        }
        if (!$valid) {
            $this->_addMessages([self::ERROR_COUNTRY_IS_EMPTY]);
        }
        return $valid;
    }

    /**
     * Validate by country
     *
     * @param array $value
     * @return bool
     */
    protected function isCountryValid($value)
    {
        $countries = $this->countryHelper->toOptionArray();
        foreach ( $countries as $countryKey => $country ) {
            if ($value == $countryKey || $value == $country) {
                return true;
            }
        }
        return false;
    }

    public function getCountryByName($countryName)
    {
        $countries = $this->countryHelper->toOptionArray();
        foreach ( $countries as $countryKey => $country ) {
            if ($country != $countryName) {
                return $countryKey;
            }
        }
        return '';
    }
}