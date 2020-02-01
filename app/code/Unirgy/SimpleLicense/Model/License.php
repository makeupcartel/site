<?php

namespace Unirgy\SimpleLicense\Model;

use \Magento\Framework\Model\AbstractModel;

/**
 * Class License
 * @method License setLastStatus(string $status)
 * @method License setLastError(string $error)
 * @method License setRetryNum(int $num)
 * @method License setSignature(string $signature)
 * @method License setAuxChecksum(string $checksum)
 * @method License setLicenseStatus(string $status)
 * @method string getLicenseKey()
 * @method string getAuxChecksum()
 * @method string getLicenseExpire()
 * @method string getLicenseStatus()
 * @method string getServerRestriction()
 * @method int getRetryNum()
 * @method array getProducts()
 * @package Unirgy\SimpleLicense\Model
 */
class License extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Unirgy\SimpleLicense\Model\ResourceModel\License');
    }

    public function getServerRestriction1()
    {
        return $this->getData('server_restriction1');
    }

    public function getServerRestriction2()
    {
        return $this->getData('server_restriction2');
    }

    public function getModules()
    {
        return explode("\n", $this->getData('products'));
    }

    public function __get($name)
    {
        return $this->_getData($name);
    }

    public function __set($name, $value)
    {
        return $this->setData($name, $value);
    }

    public function __isset($name)
    {
        return $this->hasData($name);
    }
}
