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

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Config as ModelConfig;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\Source\AbstractSource;

/**
 * Class Source
 *
 * @method Source setPath(\string $path)
 * @package Unirgy\RapidFlow\Model
 */
class Source extends AbstractSource
{
    /**
     * @var HelperData
     */
    protected $_rapidFlowHelper;

    /**
     * @var Config
     */
    protected $_rapidFlowConfig;

    /**
     * @var ModelConfig
     */
    protected $_eavConfig;

    /**
     * @var Set
     */
    protected $_entityAttributeSet;
    /**
     * @var StoreManager
     */
    protected $_storeManager;
    protected $_withDefaultWebsite = true;

    public function __construct(
        StoreManagerInterface $storeManager,
        Set $entityAttributeSet,
        HelperData $rapidFlowHelperData,
        Config $rapidFlowModelConfig,
        ModelConfig $eavModelConfig,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_rapidFlowHelper = $rapidFlowHelperData;
        $this->_rapidFlowConfig = $rapidFlowModelConfig;
        $this->_eavConfig = $eavModelConfig;
        $this->_entityAttributeSet = $entityAttributeSet;

        parent::__construct($data);
    }

    public function toOptionHash($selector = false)
    {
//        $hlp = $this->_rapidFlowHelper;

        $options = [];

        switch ($this->getData('path')) {
            case 'yesno':
                $options = [
                    1 => __('Yes'),
                    0 => __('No'),
                ];
                break;

            case 'ftp_file_mode':
                $options = [
                    FTP_ASCII => __('Ascii'),
                    FTP_BINARY => __('Binary'),
                ];
                break;

            case 'profile_status':
                $options = [
                    'enabled' => __('Enabled'),
                    'disabled' => __('Disabled'),
                ];
                break;


            case 'profile_type':
                $options = [
                    'import' => __('Import'),
                    'export' => __('Export'),
                ];
                break;

            case 'urapidflow/import_options/date_processor':
                $options = [
                    'strtotime' => __('strtotime'),
                    'zend_date' => __('Zend_Date'),
                ];
                if (version_compare(phpversion(), '5.3.0', '>=')) {
                    $options['date_parse_from_format'] = __('date_parse_from_format (PHP >= 5.3.0)');
                }
                break;

            case 'run_status':
                $options = [
                    'idle' => __('Idle'),
                    'pending' => __('Pending'),
                    'running' => __('Running'),
                    'paused' => __('Paused'),
                    'stopped' => __('Stopped'),
                    'finished' => __('Finished'),
                ];
                break;

            case 'invoke_status':
                $options = [
                    'none' => __('None'),
                    //'foreground' => __('Foreground'),
                    'ondemand' => __('On Demand'),
                    //'scheduled' => __('Scheduled'),
                ];
                break;

            case 'data_type':
                $dataTypes = $this->_rapidFlowConfig->getDataTypes();
                foreach ($dataTypes as $k => $c) {
                    $options[$k] = __((string)$c->title);
                }
                break;

            case 'row_type':
                $rowTypes = $this->_rapidFlowConfig->getRowTypes($this->getDataType());
                foreach ($rowTypes as $k => $c) {
                    $label = (string)$c->title;
                    if ($this->getStripFromLabel()) {
                        $label = preg_replace($this->getStripFromLabel(), '', $label);
                    }
                    $options[$k] = $k . ': ' . __($label);
                }
                break;

            case 'stores':
                $options = $this->getStores();
                break;

            case 'schedule_hours':
                for ($i = 0; $i <= 23; $i++) {
                    $options[$i] = $i;
                }
                break;

            case 'schedule_week_days':
                for ($i = 0; $i <= 6; $i++) {
                    $options[$i] = $i;
                }
                break;

            case 'schedule_month_days':
                for ($i = 1; $i <= 31; $i++) {
                    $options[$i] = $i;
                }
                break;

            case 'schedule_months':
                for ($i = 1; $i <= 12; $i++) {
                    $options[$i] = $i;
                }
                break;

            case 'attribute_sets':
                $etId = $this->_eavConfig->getEntityType('catalog_product')->getEntityTypeId();
                $collection = $this->_entityAttributeSet->getCollection()
                    ->addFieldToFilter('entity_type_id', $etId);
                $options = [];
                foreach ($collection as $s) {
                    $options[$s->getId()] = $s->getAttributeSetName();
                }
                break;

            case 'entity_types':
                $options = [];
                foreach (['catalog_product', 'catalog_category'] as $et) {
                    $e = $this->_eavConfig->getEntityType($et);
                    $options[$e->getId()] = $e->getEntityTypeCode();
                }
                break;

            case 'encoding_illegal_char':
                $options = [
                    '' => __('Add warning and pass through as original'),
                    'TRANSLIT' => __('Attempt to transliterate'),
                    'IGNORE' => __('Remove illegal characters'),
                ];
                break;

            case 'import_actions':
                $options = [
                    'any' => __('Create or Update as neccessary'),
                    'create' => __('Only create new records'),
                    'update' => __('Only update existing records'),
                ];
                break;

            case 'import_reindex_type':
                $options = [
                    'full' => __('Full (automatically AFTER import)'),
                    'realtime' => __('Realtime (affected records DURING import)'),
                    'manual' => __('Manual (flag affected indexes)'),
                ];
                break;
            case 'import_reindex_type_nort':
                $options = [
                    'full' => __('Full (automatically AFTER import)'),
                    'manual' => __('Manual (flag affected indexes)'),
                ];
                break;

            case 'import_image_remote_subfolder_level':
                $options = [
                    '' => __('No subfolders (image.jpg)'),
                    '1' => __('1 subfolder (a/image.jpg)'),
                    '2' => __('2 subfolders (a/b/image.jpg)'),
                    '3' => __('3 subfolders (a/b/c/image.jpg)'),
                ];
                break;

            case 'import_image_missing_file':
                $options = [
                    'warning_save' => __('WARNING and update image field'),
                    'warning_skip' => __('WARNING and skip image field update'),
                    'warning_empty' => __('WARNING and set image field as empty'),
                    'error' => __('ERROR and skip the whole record update'),
                ];
                break;

            case 'import_image_existing_file':
                $options = [
                    'skip' => __('WARNING and skip image field update'),
                    'replace' => __('WARNING and replace existing image'),
                    'save_new' => __('WARNING and save image as new by appending suffix'),
                ];
                break;

            case 'store_value_same_as_default':
                $options = [
                    'default' => __('Use default values'),
                    'duplicate' => __('Create the values for store level'),
                ];
                break;

            case 'category_display_mode':
                $options = [
                    Category::DM_PRODUCT => __('Products only'),
                    Category::DM_PAGE => __('Static block only'),
                    Category::DM_MIXED => __('Static block and products'),
                ];
                break;

            case 'log_level':
                $options = [
                    'SUCCESS' => __('Successful Updates'),
                    'WARNING' => __('Warnings'),
                    'ERROR' => __('Errors'),
                ];
                break;

            case 'remote_type':
                $options = [
                    '' => __('* None'),
                    'ftp' => __('FTP'),
                    'sftp' => __('SFTP'),// using phpseclib
                ];
                /*
                if (function_exists('ftp_ssl_connect')) {
                    $options['ftps'] = __('FTPS');
                }
                if (function_exists('ssh2_sftp')) {
                    $options['sftp'] = __('SFTP');
                }
                $options['http'] = __('HTTP');
                */
                break;

            case 'compress_type':
                $options = [
                    '' => __('* None'),
                    'gz' => __('gz'),
                    'bz2' => __('bz2'),
                    'zip' => __('zip'),
                ];
                break;

            case 'encoding':
                $options = ['' => __('* No enconding conversion (UTF-8)')];
                /*
                if (function_exists('mb_detect_encoding')) {
                    $options['auto'] = __('* Automatic conversion (slowest)');
                }
                    $encodings = mb_list_encodings();
                    natsort($encodings);
                    foreach ($encodings as $e) {
                        if ($e=='pass' || $e=='auto') {
                            continue;
                        }
                        $options[$e] = $e;
                    }
                */
                $options += [
                    'ISO (Unix/Linux)' => [
                        'iso-8859-1' => 'iso-8859-1',
                        'iso-8859-2' => 'iso-8859-2',
                        'iso-8859-3' => 'iso-8859-3',
                        'iso-8859-4' => 'iso-8859-4',
                        'iso-8859-5' => 'iso-8859-5',
                        'iso-8859-6' => 'iso-8859-6',
                        'iso-8859-7' => 'iso-8859-7',
                        'iso-8859-8' => 'iso-8859-8',
                        'iso-8859-9' => 'iso-8859-9',
                        'iso-8859-10' => 'iso-8859-10',
                        'iso-8859-11' => 'iso-8859-11',
                        'iso-8859-12' => 'iso-8859-12',
                        'iso-8859-13' => 'iso-8859-13',
                        'iso-8859-14' => 'iso-8859-14',
                        'iso-8859-15' => 'iso-8859-15',
                        'iso-8859-16' => 'iso-8859-16',
                    ],
                    'WINDOWS' => [
                        'windows-1250' => 'windows-1250 - Central Europe',
                        'windows-1251' => 'windows-1251 - Cyrillic',
                        'windows-1252' => 'windows-1252 - Latin I',
                        'windows-1253' => 'windows-1253 - Greek',
                        'windows-1254' => 'windows-1254 - Turkish',
                        'windows-1255' => 'windows-1255 - Hebrew',
                        'windows-1256' => 'windows-1256 - Arabic',
                        'windows-1257' => 'windows-1257 - Baltic',
                        'windows-1258' => 'windows-1258 - Viet Nam',
                    ],
                    'DOS' => [
                        'cp437' => 'cp437 - Latin US',
                        'cp737' => 'cp737 - Greek',
                        'cp775' => 'cp775 - BaltRim',
                        'cp850' => 'cp850 - Latin1',
                        'cp852' => 'cp852 - Latin2',
                        'cp855' => 'cp855 - Cyrylic',
                        'cp857' => 'cp857 - Turkish',
                        'cp860' => 'cp860 - Portuguese',
                        'cp861' => 'cp861 - Iceland',
                        'cp862' => 'cp862 - Hebrew',
                        'cp863' => 'cp863 - Canada',
                        'cp864' => 'cp864 - Arabic',
                        'cp865' => 'cp865 - Nordic',
                        'cp866' => 'cp866 - Cyrylic Russian (used in IE "Cyrillic (DOS)" )',
                        'cp869' => 'cp869 - Greek2',
                    ],
                    'MAC (Apple)' => [
                        'x-mac-cyrillic' => 'x-mac-cyrillic',
                        'x-mac-greek' => 'x-mac-greek',
                        'x-mac-icelandic' => 'x-mac-icelandic',
                        'x-mac-ce' => 'x-mac-ce',
                        'x-mac-roman' => 'x-mac-roman',
                    ],
                    'MISCELLANEOUS' => [
                        'gsm0338' => 'gsm0338 (ETSI GSM 03.38)',
                        'cp037' => 'cp037',
                        'cp424' => 'cp424',
                        'cp500' => 'cp500',
                        'cp856' => 'cp856',
                        'cp875' => 'cp875',
                        'cp1006' => 'cp1006',
                        'cp1026' => 'cp1026',
                        'koi8-r' => 'koi8-r (Cyrillic)',
                        'koi8-u' => 'koi8-u (Cyrillic Ukrainian)',
                        'nextstep' => 'nextstep',
                        'us-ascii' => 'us-ascii',
                        'us-ascii-quotes' => 'us-ascii-quotes',
                    ],
                ];
                break;

            case 'save_attributes_method':
            case 'urapidflow/finetune/save_attributes_method':
                $options = [
                    '' => __('Plain'),
                    'PDOStatement' => __('PDOStatement'),
                ];
                break;

            case 'empty_value_strategy':
                $options = [
                    '' => 'Use default value for new rows, leave existing row value intact',
                    #'default' => 'Use default value if exists',
                    'empty' => 'Set empty value always',
                ];
                break;

            default:
                throw new LocalizedException(__('Invalid request for source options: ' . $this->getData('path')));
        }

        if ($selector) {
            $options = ['' => __('* Please select')] + $options;
        }

        return $options;
    }

    public function getStores()
    {
        $options = [];
        /** @var Website $website */
        foreach ($this->_storeManager->getWebsites((bool)$this->_withDefaultWebsite) as $website) {
            /**
             * @var int $sId
             * @var Store $store
             */
            foreach ($website->getStores() as $sId => $store) {
                $options[$website->getName()][$sId] = '[' . $store->getCode() . '] ' . $store->getName();
            }
        }
        return $options;
    }

    public function toOptionArray($selector = false)
    {
        switch ($this->getData('path')) {

        }
        return parent::toOptionArray($selector);
    }

    public function withDefaultWebsite($flag)
    {
        $oldFlag = $this->_withDefaultWebsite;
        $this->_withDefaultWebsite = (bool)$flag;
        return $oldFlag;
    }
}
