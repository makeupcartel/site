<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Geoip
 */


namespace Amasty\Geoip\Model;

use Amasty\Geoip\Helper\Data;
use Exception;
use Magento\Backend\Model\Session;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Import extends AbstractModel
{
    /**
     * @var string
     */
    protected static $_sessionKey = 'am_geoip_import_process_%key%';

    /**
     * @var string
     */
    protected $_tablePrefix;

    /**
     * @var array
     */
    protected $_modelsCols = [
        'block' => [
            'start_ip_num',
            'end_ip_num',
            'geoip_loc_id',
            'postal_code',
            'latitude',
            'longitude'
        ],
        'block_v6' => [
            'start_ip_num',
            'end_ip_num',
            'geoip_loc_id',
            'postal_code',
            'latitude',
            'longitude'
        ],
        'location' => [
            'geoip_loc_id',
            'country',
            'city'
        ]
    ];

    /**
     * @var int
     */
    protected $_rowsPerTransaction = 10000;

    /**
     * Resource model of config data
     *
     * @var ConfigInterface
     */
    protected $configInterface;

    /**
     * Database write connection
     *
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Core Date
     *
     * @var DateTime
     */
    protected $coreDate;

    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var DeploymentConfig $_deploymentConfig
     */
    protected $_deploymentConfig;

    /**
     * @var ReinitableConfigInterface $reinitableInterface
     */
    protected $reinitableInterface;

    /**
     * @var Data $helper
     */
    protected $helper;

    /**
     * @var bool
     */
    protected $_firstTime = true;

    /**
     * Import constructor.
     * @param ConfigInterface $configInterface
     * @param ReinitableConfigInterface $reinitableConfig
     * @param ResourceConnection $resource
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $coreDate
     * @param Session $backendSession
     * @param DeploymentConfig $deploymentConfig
     * @param Data $helper
     */
    public function __construct(
        ConfigInterface $configInterface,
        ReinitableConfigInterface $reinitableConfig,
        ResourceConnection $resource,
        ScopeConfigInterface $scopeConfig,
        DateTime $coreDate,
        Session $backendSession,
        DeploymentConfig $deploymentConfig,
        Data $helper
    ) {
        $this->configInterface = $configInterface;
        $this->reinitableInterface = $reinitableConfig;
        $this->resource = $resource;
        $this->scopeConfig = $scopeConfig;
        $this->coreDate = $coreDate;
        $this->backendSession = $backendSession;
        $this->_deploymentConfig = $deploymentConfig;
        $this->helper = $helper;
    }

    /**
     * @param $table
     * @return string
     */
    protected static function getSessionKey($table)
    {
        return strtr(self::$_sessionKey, ['%key%' => $table]);
    }

    /**
     * @param $table
     * @param $filePath
     * @param $action
     * @param int $ignoredLines
     *
     * @return array
     *
     * @throws Exception
     */
    public function startProcess($table, $filePath, $action, $ignoredLines = 0)
    {
        $allowedTableTypes = ['block', 'location', 'block_v6'];
        if (!in_array($table, $allowedTableTypes)) {
            throw new Exception('Invalid table type');
        }

        $importProcess = [
            'position' => 0,
            'tmp_table' => null,
            'rows_count' => $this->_getRowsCount($filePath),
            'current_row' => 0
        ];
        $write = $this->resource->getConnection();
        $query = 'SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE \'%amasty_geoip_' . $table . '_%\'';
        $columns = $write->fetchCol($query);
        foreach ($columns as $key => $column) {
            if ($table == 'block' && preg_match('/.+_block_v6/', $column)) {
                unset($columns[$key]);
            }
        }
        $oldTemporary = implode(', ', $columns);
        if (!empty($oldTemporary)) {
            $delete = "DROP TABLE IF EXISTS $oldTemporary";
            $write->query($delete);
        }

        if (($handle = fopen($filePath, "r")) !== false) {
            $tmpTableName = $this->_prepareImport($table);

            while ($ignoredLines > 0 && ($data = fgetcsv($handle, 0, ",")) !== false) {
                $ignoredLines--;
            }

            $importProcess['position'] = ftell($handle);
            $importProcess['tmp_table'] = $tmpTableName;
        }

        $this->_truncateTable('amasty_geoip_block');
        $this->_truncateTable('amasty_geoip_block_v6');
        $this->_truncateTable('amasty_geoip_location');

        $this->helper->flushConfigCache();
        $this->_saveInDb($table, $importProcess);

        return $importProcess;
    }

    /**
     * @return bool
     */
    public function importTableHasData()
    {
        $write = $this->resource->getConnection();
        $blockTable = $this->resource->getTableName('amasty_geoip_block');
        $blockV6Table = $this->resource->getTableName('amasty_geoip_block_v6');
        $locationTable = $this->resource->getTableName('amasty_geoip_location');
        $countBlock = "SELECT count(block_id) as count_block_id FROM $blockTable";
        $countBlockV6 = "SELECT count(block_id) as count_block_id FROM $blockV6Table";
        $countLocation = "SELECT count(location_id) as count_location_id FROM $locationTable";
        $block = $write->fetchCol($countBlock);
        $blockV6 = $write->fetchCol($countBlockV6);
        $location = $write->fetchCol($countLocation);

        return array_key_exists(0, $block)
            && array_key_exists(0, $blockV6)
            && array_key_exists(0, $location)
            && $block[0]
            && $location[0];
    }

    /**
     * @param $filePath
     * @return int
     */
    protected function _getRowsCount($filePath)
    {
        $lineCount = 0;
        $handle = fopen($filePath, "r");
        while (!feof($handle)) {
            fgets($handle);
            $lineCount++;
        }
        fclose($handle);

        return $lineCount;
    }

    /**
     * @param $table
     * @return string
     */
    protected function _prepareImport($table)
    {
        $write = $this->resource->getConnection();

        $targetTable = $this->resource->getTableName('amasty_geoip_' . $table);

        $tmpTableName = uniqid($targetTable . '_');

        $query = 'create table ' . $tmpTableName . ' like ' . $targetTable;
        $write->query($query);

        $query = 'alter table ' . $tmpTableName . ' engine innodb';
        $write->query($query);

        return $tmpTableName;
    }

    /**
     * @param $table
     * @param $filePath
     * @param $action
     * @return array
     * @throws Exception
     */
    public function doProcess($table, $filePath, $action)
    {
        $ret = [];
        if (($handle = fopen($filePath, "r")) !== false) {
            $this->helper->flushConfigCache();
            $importProcess = $this->_getFromDb($table);
            $write = $this->resource->getConnection();

            if (!$importProcess) {
                throw new Exception('run start before');
            }
            $tmpTableName = $importProcess['tmp_table'];
            try {
                $position = $importProcess['position'];
                fseek($handle, $position);
                $transactionIterator = 0;
                $write->beginTransaction();
                if ($action != 'import') {
                    $this->_tablePrefix = (string)$this->_deploymentConfig->get(
                        ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX
                    );
                }
                while (($data = fgetcsv($handle, 0, ",")) !== false) {
                    $this->_importItem($table, $tmpTableName, $data);
                    $transactionIterator++;
                    if ($transactionIterator >= $this->_rowsPerTransaction) {
                        break;
                    }
                }
                $write->commit();
                if ($this->_rowsPerTransaction > $importProcess['rows_count']) {
                    $importProcess['current_row'] = $importProcess['rows_count'];
                }
                $importProcess['current_row'] += $transactionIterator;
                $importProcess['position'] = ftell($handle);
//                $this->helper->flushConfigCache();
                $this->_saveInDb($table, $importProcess);
                $ret = $importProcess;
            } catch (Exception $e) {
                $write->rollback();
                if ($action == 'import') {
                    $this->_destroyImport($tmpTableName);
                }
                throw new Exception($e->getMessage());
            }
        }

        return $ret;
    }

    /**
     * @param $table
     * @param bool $isDownload
     * @return bool
     * @throws Exception
     */
    public function commitProcess($table, $isDownload = false)
    {
        $ret = false;
        $this->helper->flushConfigCache();
        $importProcess = $this->_getFromDb($table);

        if ($importProcess) {
            $tmpTableName = $importProcess['tmp_table'];

            if ($isDownload) {
                $configDate = 'date_download';
            } else {
                $configDate = 'date';
            }

            try {
                $this->configInterface
                    ->saveConfig('amgeoip/import/' . $table, 1, 'default', 0)
                    ->saveConfig('amgeoip/import/' . $configDate, $this->coreDate->gmtDate(), 'default', 0);

                $this->reinitableInterface->reinit();

                $this->_doneImport($table, $tmpTableName);

            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            $ret = true;
        } else {
            throw new Exception('run start before');
        }

        return $ret;
    }

    /**
     * @param $table
     * @param $tmpTableName
     */
    protected function _doneImport($table, $tmpTableName)
    {
        $write = $this->resource->getConnection();

        $targetTable = $this->resource
            ->getTableName('amasty_geoip_' . $table);

        if ($write->isTableExists($tmpTableName)) {
            $query = 'delete from ' . $targetTable;
            $write->query($query);

            $query = 'insert into ' . $targetTable . ' select * from ' . $tmpTableName;
            $write->query($query);

            $query = 'drop table if exists ' . $tmpTableName;
            $write->query($query);
        }
    }

    /**
     * @param $table
     */
    protected function _truncateTable($table)
    {
        $write = $this->resource->getConnection();
        $write->truncateTable($this->resource->getTableName($table));
    }

    /**
     * @param $table
     * @param $tmpTableName
     * @param $data
     * @param $action
     */
    protected function _importItem($table, $tmpTableName, &$data)
    {
        $write = $this->resource->getConnection();

        if ($table == 'block' && is_array($data) && isset($data[0])) {
            list($ip, $mask) = explode('/', $data[0]);
            $ip2long = ip2long($ip);
            $min = ($ip2long >> (32 - $mask)) << (32 - $mask);
            $max = $ip2long | ~(-1 << (32 - $mask));
            $newData = [$min, $max, $data[1], $data[6], $data[7], $data[8]];
            $data = $newData;
        } elseif ($table == 'location' && is_array($data)) {
            $newData = [$data[0], $data[4], $data[10]];
            $data = $newData;
        } elseif ($table == 'block_v6' && is_array($data) && isset($data[0])) {
            list($ip, $mask) = explode('/', $data[0]);

            $firstAddrBin = inet_pton($ip);
            $elem = unpack('H*', $firstAddrBin);
            $firstAddrHex = reset($elem);
            $firstAddrStr = inet_ntop($firstAddrBin);
            $flexBits = 128 - $mask;
            $lastAddrHex = $firstAddrHex;
            $pos = 31;

            while ($flexBits > 0) {
                $orig = substr($lastAddrHex, $pos, 1);
                $origVal = hexdec($orig);
                $newVal = $origVal | (pow(2, min(4, $flexBits)) - 1);
                $new = dechex($newVal);
                $lastAddrHex = substr_replace($lastAddrHex, $new, $pos, 1);
                $flexBits -= 4;
                $pos--;
            }

            $lastAddrBin = pack('H*', $lastAddrHex);
            $lastAddrStr = inet_ntop($lastAddrBin);

            $newData = [
                $this->helper->getLongIpV6($firstAddrStr),
                $this->helper->getLongIpV6($lastAddrStr),
                $data[1],
                $data[6],
                $data[7],
                $data[8]
            ];
            $data = $newData;
        }

        $query = 'insert into `' . $tmpTableName . '`' .
            '(`' . implode('`, `', $this->_modelsCols[$table]) . '`) VALUES ' .
            '(?)';

        $query = $write->quoteInto($query, $data);

        $write->query($query);
    }

    /**
     * @param $table
     * @param $tmpTableName
     */
    protected function _destroyImport($tmpTableName)
    {
        $write = $this->resource->getConnection();

        $query = 'drop table if exists ' . $tmpTableName;
        $write->query($query);

        $this->_clearDb();
    }

    /**
     * @param $table
     * @param $importProcess
     */
    protected function _saveInDb($table, $importProcess)
    {
        $this->configInterface->saveConfig('amgeoip/import/position/' . $table, $importProcess['position'], 'default', 0);
        $this->configInterface->saveConfig('amgeoip/import/tmp_table/' . $table, $importProcess['tmp_table'], 'default', 0);
        $this->configInterface->saveConfig('amgeoip/import/rows_count/' . $table, $importProcess['rows_count'], 'default', 0);
        $this->configInterface->saveConfig('amgeoip/import/current_row/' . $table, $importProcess['current_row'], 'default', 0);
    }

    /**
     * @param $table
     * @return array
     */
    protected function _getFromDb($table)
    {
        $importProcess = [];
        $importProcess['position'] = $this->scopeConfig->getValue('amgeoip/import/position/' . $table, 'default', 0);
        $importProcess['tmp_table'] = $this->scopeConfig->getValue('amgeoip/import/tmp_table/' . $table, 'default', 0);
        $importProcess['rows_count'] = $this->scopeConfig->getValue('amgeoip/import/rows_count/' . $table, 'default', 0);
        $importProcess['current_row'] = $this->scopeConfig->getValue('amgeoip/import/current_row/' . $table, 'default', 0);
        return $importProcess;
    }

    /**
     *
     */
    protected function _clearDb()
    {
        $this->configInterface->deleteConfig('amgeoip/import/position/location', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/position/block', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/position/block_v6', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/tmp_table/location', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/tmp_table/block', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/tmp_table/block_v6', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/rows_count/location', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/rows_count/block', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/rows_count/block_v6', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/current_row/location', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/current_row/block', 'default', 0);
        $this->configInterface->deleteConfig('amgeoip/import/current_row/block_v6', 'default', 0);
    }
}
