<?php

namespace Unirgy\SimpleUp\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Module
 * @package Unirgy\SimpleUp\Model
 * @method Module setModuleName(string $modName)
 * @method Module setDownloadUri(string $url)
 * @method Module setLastDownloaded(string $date)
 * @method Module setLastChecked(string $date)
 * @method Module setLastVersion(string $version)
 * @method Module setRemoteVersion(string $version)
 * @method string getModuleName()
 * @method string getDownloadUri()
 * @method string getLicenseKey()
 */
class Module extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('Unirgy\SimpleUp\Model\ResourceModel\Module');
    }
}
