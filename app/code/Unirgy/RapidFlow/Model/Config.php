<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Model;

use Magento\Framework\App\Cache\Proxy;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Config\Base;
use Magento\Framework\App\Config\Element;
use Magento\Framework\Exception\LocalizedException;
use Unirgy\RapidFlow\Helper\Data;
use Unirgy\RapidFlow\Model\Config\Reader as RfReader;

/**
 * Class Config
 * @package Unirgy\RapidFlow\Model
 */
class Config extends Base
{
    /**
     * Cache tag and cache id
     */
    const CACHE_TAG = \Unirgy\RapidFlow\Model\CacheType::CACHE_TAG;

    const CACHE_ID = \Unirgy\RapidFlow\Model\CacheType::TYPE_IDENTIFIER;

    /**
     * @var array
     */
    protected $_rowTypeColumns = [];

    /**
     * @var Proxy
     */
    protected $_cacheProxy;

    /**
     * @var StateInterface
     */
    protected $_cacheState;

    /**
     * @var RfReader
     */
    protected $_urfConfigReader;

    /**
     * Config constructor.
     * @param Proxy $cache
     * @param StateInterface $cacheState
     * @param RfReader $reader
     * @param null $sourceData
     */
    public function __construct(
        Proxy $cache,
        StateInterface $cacheState,
        RfReader $reader,
        $sourceData = null
    ) {
        $this->_cacheProxy = $cache;
        $this->_cacheState = $cacheState;
        $this->_urfConfigReader = $reader;

        parent::__construct($sourceData);
    }

    /**
     * @return Proxy
     */
    public function getCache()
    {
        return $this->_cacheProxy;
    }

    protected $_isLoaded=false;
    protected function _loadData()
    {
        if (!$this->_isLoaded) {
            $cacheId = self::CACHE_ID;
            $cachedData = $this->_rfLoadCache();
            if (empty($cachedData) || !$this->loadString($cachedData)) {
                $config = $this->_urfConfigReader->read();
                $this->setXml($config->descend('urapidflow'));
                $this->_rfSaveCache($config->asXML());
            } else {
                $this->setXml($this->getXml()->descend('urapidflow'));
            }
            $this->_isLoaded = true;
        }
        return $this;
    }

    protected function _rfLoadCache()
    {
        if (!$this->_cacheState->isEnabled(self::CACHE_ID)) {
            return false;
        }
        $cacheData = $this->getCache()->load(self::CACHE_ID);
        return $cacheData;
    }

    protected function _rfSaveCache($data)
    {
        if (!$this->_cacheState->isEnabled(self::CACHE_ID)) {
            return false;
        }
        $this->getCache()->save($data, self::CACHE_ID, [self::CACHE_TAG]);
        return $this;
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getDataTypes()
    {
        $this->_loadData();
        return $this->getNode('data_types')->children();
    }

    /**
     * @param null $dataType
     * @return array|\SimpleXMLElement
     */
    public function getRowTypes($dataType = null)
    {
        $this->_loadData();
        $nodes = $this->getNode('row_types')->children();
        if (!$dataType) {
            return $nodes;
        }
        $rowTypes = [];
        /** @var Element $node */
        foreach ($nodes as $k => $node) {
            $restrictMagentoVersion = $node->descend('restrictions/magento_version');
            if ($restrictMagentoVersion && version_compare(Data::getVersion(), (string)$restrictMagentoVersion, '<')) {
                continue;
            }
            if ($dataType !== (string)$node->data_type) {
                continue;
            }
            $rowTypes[$k] = $node;
        }
        return $rowTypes;
    }

    /**
     * @param $profileType
     * @param $dataType
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    public function getProfileTabs($profileType, $dataType)
    {
        $this->_loadData();
        $tabs = $this->getNode("data_types/$dataType/profile/$profileType/tabs");
        if (!$tabs) {
            throw new LocalizedException(__("Invalid data type '%1' or profile type '%2'", $dataType, $profileType));
        }
        return $tabs->children();
    }

    /**
     * Maintain file columns cache
     *
     * @param string $rowType
     * @return array
     */
    public function getRowTypeColumns($rowType)
    {
        $this->_loadData();
        if (empty($this->_rowTypeColumns[$rowType])) {
            $node = $this->getNode("row_types/$rowType/columns");
            if (!$node) {
                var_dump($rowType);
                exit;
            }
            $columnsConfig = $node->asArray();
            if (!$columnsConfig) {
                return false;
            }
            uasort($columnsConfig, [$this, '_sortColumnsCb']);
            $this->_rowTypeColumns[$rowType] = $columnsConfig;
        }
        return $this->_rowTypeColumns[$rowType];
    }

    /**
     * Sort columns by 'col' member
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortColumnsCb($a, $b)
    {
        return empty($a['col']) || empty($b['col']) || $a['col'] == $b['col'] ? 0 : ($a['col'] < $b['col'] ? -1 : 1);
    }

}
