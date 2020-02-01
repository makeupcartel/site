<?php

namespace Unirgy\RapidFlow\Model\ResourceModel\Catalog;

use Magento\Eav\Model\Config;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\ResourceModel\AbstractResource;

abstract class AbstractCatalog extends AbstractResource
{

    protected $_attributesById = [];

    protected $_attributesByCode = [];

    protected $_attributesByType = [];

    protected function _construct() {

        parent::_construct();
        $this->_eav = $this->_eavModelConfig;
    }

    /**
     * retrieve attr record by id or code, with optional record field and value
     * @param $attribute
     * @param string $field
     * @param string $value
     * @return mixed - return attribute filed value, or entire attribute or false
     */
    protected function _attr($attribute, $field = null, $value = null)
    {
        if (isset($this->_attributesById[$attribute])) {
            $attr = $this->_attributesById[$attribute];
        } elseif (isset($this->_attributesByCode[$attribute])) {
            $attr = $this->_attributesByCode[$attribute];
        } else {
            return false;
        }
        if ($field !== null) {
            if($value !== null){
                if ($field === 'options_bytext') {
                    $value = strtolower($value);
                }
                return isset($attr[$field][$value]) ? $attr[$field][$value] : false;
            }
            return isset($attr[$field]) ? $attr[$field] : false;
        } else {
            return $attr;
        }
    }
}
