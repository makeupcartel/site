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

use Magento\Catalog\Model\Indexer\Product\Category;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image;
use Magento\Catalog\Model\ResourceModel\Category\Flat;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\Framework\App\Cache\Manager as CacheManager;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception as FrameworkException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Asset\MergeService;
use Magento\Indexer\Model\Indexer;
use Magento\Store\Model\ScopeInterface;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use Unirgy\RapidFlow\Exception;
use Unirgy\RapidFlow\Exception\Stop;
use Unirgy\RapidFlow\Helper\Data as HelperData;
use Unirgy\RapidFlow\Model\Io\AbstractIo;
use Unirgy\RapidFlow\Model\Io\File;
use Unirgy\RapidFlow\Model\Logger\AbstractLogger;
use Unirgy\RapidFlow\Model\ResourceModel\AbstractResource as ResourceModelAbstractResource;
use Zend\Json\Json;

/**
 * Class Profile
 *
 * @method  Profile setCurrentActivity(Phrase $phrase)
 * @method  Profile setInvokeStatus(string $status)
 * @method  Profile setRunStatus(string $status)
 * @method  Profile setStartedAt(string $time)
 * @method  Profile setPausedAt(string $time)
 * @method  Profile setStoppedAt(string $time)
 * @method  Profile setFinishedAt(string $time)
 * @method  Profile setSnapshotAt(string $time)
 * @method  Profile setColumns(array $cols)
 * @method  Profile setStoreId(int $id)
 * @method  Profile setRowsFound(int $id)
 * @method  Profile setMemoryUsage(int $id)
 * @method  Profile setMemoryPeakUsage(int $id)
 * @method  Profile setConditions(array $conditions)
 * @method  Profile unsPausedAt()
 * @method  bool hasRule()
 * @method  bool hasColumnsPost()
 * @method  bool getUseTransactions()
 * @method  array getColumnsPost()
 * @method  array getColumns()
 * @method  string getConditions()
 * @method  string getFilename()
 * @method  string getRunStatus()
 * @method  string getProfileStatus()
 * @method  string getProfileType()
 * @method  string getJsonImport()
 * @method  string getRule()
 * @method  string getBaseDir()
 * @method  string getSnapshotAt()
 * @method  string getStartedAt()
 * @method  string getCurrentActivity()
 * @method  int getSkipReindex()
 * @method  int getStoreId()
 * @method  int getRowsFound()
 * @method  int getRowsProcessed()
 * @method  int getRowsSuccess()
 * @method  int getRowsDepends()
 * @method  int getRowsNochange()
 * @method  int getRowsEmpty()
 * @method  int getRowsErrors()
 * @method  int getNumErrors()
 * @method  int getNumWarnings()
 * @method  int getMemoryUsage()
 * @method  int getMemoryPeakUsage()
 * @method  ResourceModel\Profile _getResource()()
 * @package Unirgy\RapidFlow\Model
 */
class Profile extends AbstractModel
{
    const NUM_WARNINGS   = 'num_warnings';
    const NUM_ERRORS     = 'num_errors';
    const ROWS_EMPTY     = 'rows_empty';
    const ROWS_SUCCESS   = 'rows_success';
    const ROWS_NOCHANGE  = 'rows_nochange';
    const ROWS_DEPENDS   = 'rows_depends';
    const ROWS_PROCESSED = 'rows_processed';
    const ROWS_ERRORS    = 'rows_errors';

    /**
     * @var Config
     */
    protected $_rapidFlowConfig;

    /**
     * @var HelperData
     */
    protected $_rapidFlowHelper;

    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    /**
     * @var WriteFactory
     */
    protected $_directoryWrite;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var Rule
     */
    protected $_rapidFlowModelRule;

    /**
     * @var CacheManager
     */
    protected $_cacheManager;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var File
     */
    protected $_ioFile;

    /**
     * @var IndexerRegistry
     */
    protected $_indexerRegistry;

    /**
     * @var Image
     */
    protected $_imageModel;

    /**
     * @var Status
     */
    protected $_modelStockStatus;

    /**
     * @var \Magento\Framework\View\Asset\MergeService
     */
    protected $_mergedService;

    protected $_resourceModel;

    protected $_defaultIoModel = 'Unirgy\RapidFlow\Model\Io\Csv';

    protected $_defaultLoggerModel = 'Unirgy\RapidFlow\Model\Logger\Csv';

    protected $_altLoggerModel = 'Unirgy\RapidFlow\Model\Logger\CsvAlt';

    protected $_saveFields = [
        'snapshot_at',
        'rows_found',
        'rows_processed',
        'rows_success',
        'rows_nochange',
        'rows_empty',
        'rows_depends',
        'rows_errors',
        'num_errors',
        'num_warnings',
        'memory_usage',
        'memory_peak_usage'
    ];

    protected $_loadFields = ['run_status'];

    protected $_jsonFields = [
        'columns' => 'columns_json',
        'options' => 'options_json',
        'conditions' => 'conditions_json',
        'profile_state' => 'profile_state_json',
    ];

    protected $_jsonImportFields = [
        "columns",
        "options",
        "conditions",
    ];

    protected $_defaults = [
        'options' => [
            'csv' => [
                'delimiter' => ',',
                'enclosure' => '"',
                'escape' => '\\',
                'multivalue_separator' => ';',
            ],
            'encoding' => [
                'from' => 'UTF-8',
                'to' => 'UTF-8',
            ],
        ],
    ];

    protected $_gt14;

    protected $_lastSync;

    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    public function __construct(
        Context $context,
        Registry $registry,
        MergeService $mergeService,
        IndexerRegistry $indexerRegistry,
        ResolverInterface $localeResolver,
        TimezoneInterface $localeDate,
        Config $rapidFlowConfig,
        HelperData $rapidFlowHelper,
        DirectoryList $directoryList,
        WriteFactory $directoryWrite,
        Rule $rapidFlowModelRule,
        CacheManager $cacheManager,
        Filesystem $magentoFilesystem,
        ScopeConfigInterface $scopeConfig,
        File $modelIoFile,
        Image $modelProductImage,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_rapidFlowConfig = $rapidFlowConfig;
        $this->_rapidFlowHelper = $rapidFlowHelper;
        $this->_directoryList = $directoryList;

        $this->_directoryWrite = $directoryWrite;
        $this->_rapidFlowModelRule = $rapidFlowModelRule;
        $this->_cacheManager = $cacheManager;
        $this->_filesystem = $magentoFilesystem;
        $this->_scopeConfig = $scopeConfig;
        $this->_ioFile = $modelIoFile;
        $this->_indexerRegistry = $indexerRegistry;
        $this->_imageModel = $modelProductImage;
        $this->_mergedService = $mergeService;
        $this->_localeResolver = $localeResolver;
        $this->_localeDate = $localeDate;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return array|mixed
     */
    public function getAttributeCodes()
    {
        $codes = $this->getData('attribute_codes');
        if(!$codes) {
            $codes = [];
        }
        return $codes;
    }

    protected function _construct()
    {
        $this->_init('Unirgy\RapidFlow\Model\ResourceModel\Profile');
    }

    public function factory()
    {
        $dataTypes = $this->_rapidFlowConfig->getDataTypes();
        $type = $this->getDataType();
        if (!$type) {
            return $this;
            //throw new \Exception('Data type is not set');
        }
        $model = $dataTypes->descend("{$type}/profile/model");
        if (!$model) {
            return $this;
        }
        $object = $this->getObjectManager()->create($model);
        if (!$object) {
            throw new \Exception(__('Invalid profile model: %1', $model));
        }
        $object->setData($this->getData());
        return $object;
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        if ($this->_objectManager == null) {
            $this->_objectManager = ObjectManager::getInstance();
        }
        return $this->_objectManager;
    }

    public function addValue($k, $v = 1)
    {
        $this->setData($k, $this->_getData($k) + $v);
        return $this;
    }

    public function getDataTypeModel()
    {
        $root = $this->_rapidFlowConfig->getDataTypes();
        $dataType = $this->getDataType();
        if (!isset($root->$dataType) || !isset($root->$dataType->model)) {
            throw new LocalizedException(__('Invalid data type model'));
        }
        return $this->getObjectManager()->create((string)$root->$dataType->model);
    }

    public function getLogTail($length = 1000)
    {
        try {
            $io = $this->getLogger()->start('r')->seek(-$length, SEEK_END)->getIo();
        } catch (\Exception $e) {
            return [];
        }
        $tail = [];
        while ($t = $io->read()) {
            if (sizeof($t) !== 4 || !in_array($t[0], ['ERROR', 'WARNING', 'SUCCESS'])) {
                continue;
            }
            $tail[] = ['type' => $t[0], 'line' => $t[1], 'col' => $t[2], 'msg' => $t[3]];
        }
        return $tail;
    }

    protected function _run()
    {
        /** @var ResourceModelAbstractResource $res */
        $res = $this->getDataTypeModel();
        $res->setProfile($this);

        if ($this->_rapidFlowHelper->hasEeGwsFilter()
            && !$this->getObjectManager()->get('Magento\AdminGws\Model\Role')->hasStoreAccess($this->getStoreId())
        ) {
            throw new \Exception(__('You are not allowed to run this profile'));
        }

        if ($this->getProfileType() == 'import') {
            $res->import();
        } else {
            $res->export();
        }
    }

    public function run()
    {
        if ($this->isLocked()) {
            $this->_logger->warning(__('Profile is locked'));
            return $this;//TODO: (?) notify that is already running
        }

        session_write_close();
        ignore_user_abort(true);
        set_time_limit(0);
        ob_implicit_flush();

        $this->getLogger()->start('w');

        try {
            $this->lock();
            $this->_run();
        } catch (Stop $e) {
            $this->setCurrentActivity(__('Stopped'));
            $this->stop()->save();
        } catch (\Exception $e) {
            $this->setCurrentActivity(__('Error'));
            $this->addValue('num_errors');
            $this->getLogger()->error($e->getMessage());
            $this->getLogger()->error($e->getTraceAsString());
            $this->stop()->save();
            throw $e;
        }
        if ($this->getRunStatus() === 'running') {
            $this->finish()->save();

            try {
                $this->doReindexActions();
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                if ($e instanceof \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException) {
                    $this->_logger->critical(__('Conflicted URL rewrites: %1', var_export($e->getUrls(), 1)));
                }
            }

            $this->activity(__('Done'));
        }

        return $this;
    }

    public function getLockFilename()
    {
        if (!$this->hasData('lock_filename')) {
            $dir = $this->_directoryList->getPath('var') . ('/urapidflow/lock');
            $this->_directoryWrite->create($dir)->create();
            $filename = $dir . '/profile-' . $this->getId() . '.lck';
            $this->setData('lock_filename', $filename);
        }
        return $this->_getData('lock_filename');
    }

    public function lock()
    {
        // touch() didn't work for some reason...
        @file_put_contents($this->getLockFilename(), '');
        return $this;
    }

    public function unlock()
    {
        @unlink($this->getLockFilename());
        return $this;
    }

    public function isLocked()
    {
        $result = file_exists($this->getLockFilename());
        return $result;
    }

    public function activity($activity)
    {
        if ($this->getLogger()->getIo()->isOpen()) {
            $this->getLogger()->log('ACTIVITY', ['', '', $activity]);
        }
        $this->setCurrentActivity($activity)->sync(true, ['current_activity'], false);
        return $this;
    }

    protected function _beforeRun()
    {

    }

    protected function _afterRun()
    {

    }

    public function sync($force = false, $saveFields = null, $loadFields = null)
    {
        if (!$force && $this->_lastSync && $this->_lastSync >= time() - 2) {
            return false;
        }
        if (!($this->_getData('title') && $this->_getData('profile_type'))) {
            return false;
        }
        $saveFields = !is_null($saveFields) ? $saveFields : $this->_saveFields;
        $loadFields = !is_null($loadFields) ? $loadFields : $this->_loadFields;
        $this->_getResource()->sync($this, $saveFields, $loadFields);
        $this->_lastSync = time();
        return true;
    }

    public function pending($invokeStatus)
    {
        if ($this->getProfileStatus() !== 'enabled') {
            return $this;
        }

        if ($this->getProfileType() == 'import') {
            $sameType = $this->getCollection()
                ->addFieldToFilter('profile_id', ['neq' => $this->getId()])
                ->addFieldToFilter('profile_type', 'import')
                ->addFieldToFilter('data_type', $this->getDataType())
                ->addFieldToFilter('run_status', ['in' => ['pending', 'running', 'paused']]);
            if ($sameType->count()) {
                throw new \Exception(__('A profile of the same type is currently running or paused'));
            }
        }

        if (in_array($this->getRunStatus(), ['pending', 'running', 'paused'])) {
            return $this;
            #throw new Exception(__('The profile is currently running or paused'));
        }

        $this->setInvokeStatus($invokeStatus);
        $this->setRunStatus('pending')->setCurrentActivity(__('Pending'));
        $this->getLogger()->pendingProfile();

        $this->reset();

        $this->loggerStart();

        $this->_eventManager->dispatch('urapidflow_profile_action',
                                       ['action' => 'pending', 'profile' => $this]);

        return $this;
    }

    public function loggerStart()
    {
        $this->getLogger()->start('w');
        return $this;
    }

    public function loggerStartProfile()
    {
        $this->getLogger()->startProfile();
        return $this;
    }

    public function start()
    {
        if ($this->getProfileStatus() !== 'enabled') {
            return $this;
        }

        if (in_array($this->getRunStatus(), ['running', 'paused'])) {
            return $this;
            #throw new Exception(__('The profile is currently running or paused'));
        }

        if ($this->getRunStatus() !== 'pending') {
            $this->reset();
        }

        $this->setRunStatus('running');
        $this->loggerStartProfile();

        $this->_eventManager->dispatch('urapidflow_profile_action',
                                       ['action' => 'start', 'profile' => $this]);

        return $this;
    }

    public function pause()
    {
        if ($this->getRunStatus() != 'running') {
            return $this;
            #throw new Exception(__('The profile is not currently running'));
        }

        $this->unlock();

        $this->setRunStatus('paused');
        $this->getLogger()->pauseProfile();

        $this->setPausedAt(HelperData::now());

        $this->_eventManager->dispatch('urapidflow_profile_action',
                                       ['action' => 'pause', 'profile' => $this]);

        return $this;
    }

    public function resume()
    {
        if ($this->getRunStatus() != 'paused') {
            return $this;
            #throw new Exception(__('The profile is not currently paused'));
        }

        $this->setRunStatus('pending');
        $this->getLogger()->resumeProfile();

        $this->unsPausedAt();

        $this->_eventManager->dispatch('urapidflow_profile_action',
                                       ['action' => 'resume', 'profile' => $this]);

        return $this;
    }

    public function stop()
    {
        if (!in_array($this->getRunStatus(), ['pending', 'running', 'paused'])) {
            return $this;
            #throw new Exception(__('The profile is not currently running or paused'));
        }

        $this->unlock();

        $this->setRunStatus('stopped');
        $this->getLogger()->stopProfile();

        $this->setStoppedAt(HelperData::now());

        $this->_eventManager->dispatch('urapidflow_profile_action',
                                       ['action' => 'stop', 'profile' => $this]);

        return $this;
    }

    public function finish()
    {
        if ($this->getRunStatus() != 'running') {
            return $this;
            #throw new Exception(__('The profile is not currently running'));
        }

        $this->unlock();

        $this->setRunStatus('finished');
        $this->getLogger()->finishProfile();

        $this->setFinishedAt(HelperData::now());

        $this->_eventManager->dispatch('urapidflow_profile_action',
                                       ['action' => 'finish', 'profile' => $this]);

        return $this;
    }

    public function reset()
    {
        foreach ($this->_saveFields as $f) {
            $this->setData($f, 0);
        }

        $this->setStartedAt(null)->setPausedAt(null)->setStoppedAt(null)->setFinishedAt(null);

        //$this->getLogger()->reset();

        return $this;
    }

    public function getIo()
    {
        if (!$this->hasData('io')) {
            $this->setIo($this->_defaultIoModel);
        }
        return $this->getData('io');
    }

    public function setIo($io)
    {
        if (is_string($io)) {
            $io = $this->getObjectManager()->create($io);
        }

        if (!$io instanceof AbstractIo) {
            throw new \Exception(__("Wrong IO model"));
        }

        $io->setBaseDir($this->getFileBaseDir());
        $io->addData((array)$this->getData('options/csv'));
        $this->setData('io', $io);
        return $this;
    }

    public function ioOpenRead($doActionsBefore = true)
    {
        if ($doActionsBefore) {
            $this->doFileActions('before');
        }
        $this->getIo()->open($this->getFilename(), 'r');
        return $this;
    }

    public function ioSeekReset($line = 0)
    {
        $this->getIo()->seek($line);
        return $this;
    }

    public function ioTell()
    {
        return $this->getIo()->tell();
    }

    public function ioOpenWrite()
    {
        $this->getIo()->open($this->getFilename(), 'w');
        return $this;
    }

    public function ioWriteHeader($data)
    {
        $this->ioWrite($data);
        return $this;
    }

    public function ioWrite($data)
    {
        $this->getIo()->write($data);
        return $this;
    }

    public function ioRead()
    {
        return $this->getIo()->read();
    }

    public function ioClose()
    {
        $this->getIo()->close();
        $this->doFileActions('after');
    }

    /**
     * @return \Unirgy\RapidFlow\Model\Logger\Csv
     */
    public function getLogger()
    {
        if (!$this->hasData('logger')) {
            $this->setLogger($this->_defaultLoggerModel);
        }
        return $this->getData('logger');
    }

    public function setLogger($logger)
    {
        if (is_string($logger)) {
            $logger = $this->getObjectManager()->get($logger);
        }

        if (!$logger instanceof AbstractLogger) {
            throw new \Exception(__("Wrong logger model"));
        }
        $logger->setProfile($this);
        $this->setData('logger', $logger);
        return $this;
    }

    public function getConditionsRule()
    {
        if (!$this->hasData('conditions_rule')) {
            $rule = $this->_rapidFlowModelRule;
            $rule->getConditions()->setConditions([])->loadArray($this->getConditions());
            $this->setData('conditions_rule', $rule);
        }
        return $this->getData('conditions_rule');
    }

    public function getConditionsProductIds()
    {
        return $this->getConditionsRule()->getProductIds($this);
    }

    public function realtimeReindex($productIds)
    {
//        return $this; // disabled until done correctly

        if (!$this->getData('options/reindex_realtime/all') || !$productIds) {
            return $this;
        }

        $eavIndexer = $this->_indexerRegistry->get(Processor::INDEXER_ID);
        if (!$eavIndexer->isScheduled()) {
            $eavIndexer->reindexList(array_unique($productIds));
        }
        $categoryIndexer = $this->_indexerRegistry->get(Category::INDEXER_ID);
        if (!$categoryIndexer->isScheduled()) {
            $categoryIndexer->reindexList(array_unique($productIds));
        }
//        $action = $this->_productAction->setData([
//                                                     'product_ids' => array_unique($productIds),
//                                                     //'attributes_data'   => $attrData,
//                                                     'store_id' => $this->getStoreId(),
//                                                 ]);
//        $this->_indexerModel->reindexList(array_unique($productIds));

        return $this;
    }

    /**
     * For Magento 1.3.x only
     *
     */
    public function getReindexTypeNames()
    {
        return [
            'catalog_index' => __('Catalog Index'),
            'layered_navigation' => __('Layered Navigation'),
            'images_cache' => __('Images Cache'),
            'catalog_url' => __('Catalog Url Rewrites'),
            'catalog_product_flat' => __('Product Flat Data'),
            'catalog_category_flat' => __('Category Flat Data'),
            'catalogsearch_fulltext' => __('Catalog Search Index'),
            'cataloginventory_stock' => __('Stock status'),
            'catalog_rules' => __('Catalog Rules'),
        ];
    }

    public function doReindexActions()
    {
        $this->_eventManager->dispatch('urapidflow_profile_reindex_before', ['profile' => $this]);

        if ($this->getProfileType() !== 'import' || $this->getSkipReindex() || $this->getData('options/import/dryrun')) {
            return $this;
        }

        $this->_reindex();
        $this->_cacheRefresh();
        $this->_eventManager->dispatch('urapidflow_profile_reindex_after', ['profile' => $this]);
        return $this;
    }

    protected function _reindex()
    {
        $indexerRegistry = $this->_indexerRegistry;
        /* @var $indexMgr Indexer */
//        $indexMgr = $indexerRegistry->getSingleton($indexerRegistry->getIndexClassAlias());
        $processes = (array)$this->getData('options/reindex');
        try {
//            $processes = $indexMgr->getProcessesCollectionByCodes(array_keys($processes));
            $this->_eventManager->dispatch('urf_reindex_init_process');
            foreach ($processes as $process => $sortOrder) {
                /** @var \Magento\Indexer\Model\Indexer $indexer */
                $indexer = $indexerRegistry->get($process);
                if ($indexer->isWorking()) {
                    continue;
                }
                try {
                    if ($this->getData('options/import/reindex_type') === 'manual') {
                        $indexer->getState()->setStatus(StateInterface::STATUS_INVALID);
                    } else {
                        $indexer->reindexAll();
                        $this->_eventManager->dispatch($indexer->getId() . '_urf_reindex_after');
                        $this->activity($indexer->getTitle() . ' index was rebuilt successfully');
                    }
                } catch (LocalizedException $le) {
                    $this->getLogger()->log('ERROR', $le->getMessage());
                } catch (\Exception $e) {
                    $this->getLogger()->log('ERROR', $indexer->getTitle() . " index process unknown error:\n" . $e);
                    $this->getLogger()->log('ERROR', $e->getMessage() . " index process unknown error:\n" . $e);
                }
            }

//            if ($indexMgr->hasErrors()) {
//                $this->activity(implode(PHP_EOL, $indexMgr->getErrors()));
//            }

        } catch (\Exception $e) {
            $this->getLogger()->log('ERROR', $e->getMessage());
        }
        $this->_eventManager->dispatch('urf_reindex_finalize_process');
    }

    protected function _cacheRefresh()
    {
        $refresh = (array)$this->getData('options/refresh');
        $cachesToClear = [];
        foreach ($refresh as $code => $sortOrder) {
            switch ($code) {
                case 'clean_media':
                    $this->activity(__('Refreshing: %1', $code));
                    //$this->_imageModel->clearCache();
                    //$this->_eventManager->dispatch('clean_catalog_images_cache_after');
                    $this->_mergedService->cleanMergedJsCss();
                    $this->_eventManager->dispatch('clean_media_cache_after');
                    break;

                default:
                    $this->activity(__('Refreshing: %1', $code));
                    $cachesToClear[] = $code; // collect caches to clear
            }
        }
        if (!empty($cachesToClear)) {
            $this->_eventManager->dispatch('adminhtml_cache_flush_system');
            $this->_cacheManager->clean($cachesToClear);
            if (function_exists('apc_clear_cache')) { // clear apc cache
                apc_clear_cache();
                apc_clear_cache('user');
            }
        }
//        $this->_eventManager->dispatch('adminhtml_cache_flush_system');
    }

    public function doFileActions($when)
    {
        $remoteType = $this->getData('options/remote/type');
        $compressType = $this->getData('options/compress/type');
        if ($when === 'before' && $this->getProfileType() === 'import') {
            switch ($remoteType) {
                case 'ftp':
                case 'ftps':
                    $this->_ftpDownload();
                    break;
                case 'sftp':
                    $this->_sftpDownload();
                    break;
            }
            switch ($compressType) {
                case 'zip':
                    $this->_zipExtract();
                    break;
            }
        } elseif ($when === 'after' && $this->getProfileType() === 'export') {
            switch ($compressType) {
                case 'zip':
                    $this->_zipArchive();
                    break;
            }
            switch ($remoteType) {
                case 'ftp':
                case 'ftps':
                    $this->_ftpUpload();
                    break;
                case 'sftp':
                    $this->_sftpUpload();
                    break;
            }
        }
        return $this;
    }

    /**
     * @return SFTP
     * @throws LocalizedException
     */
    protected function _sftpOpen()
    {
        $remote = (array)$this->getData('options/remote');
        if (empty($remote['host'])) {
            throw new LocalizedException(__('Empty or invalid remote host name'));
        }
        $host = $remote['host'];
        $port = !empty($remote['port']) ? (int)$remote['port'] : 22;
        $timeout = !empty($remote['timeout'])? (int) $remote['ssh_timeout'] : 10;
        $sftp = new SFTP($host, $port, $timeout);

        if(empty($remote['username'])){
            throw new LocalizedException(__('Cannot establish SFTP connection without username'));
        }
        $username = $remote['username'];

        if(empty($remote['sftp_rsa_file'])){
            // no path to key file provided, using password
            $password = $remote['password'];

            $loggedIn = $sftp->login($username, $password);
        } else {
            $privateKey = new RSA();
            if(!empty($remote['sftp_rsa_passphrase'])){
                $privateKey->setPassword($remote['sftp_rsa_passphrase']);
            }

            $privateKey->loadKey(file_get_contents($remote['sftp_rsa_file']));

            $loggedIn = $sftp->login($username, $privateKey);
        }

        if(!$loggedIn){
            throw new LocalizedException(__('Failed to login to remote SFTP server'));
        }

        if (!empty($remote['path'])) {
            $result = $sftp->chdir($remote['path']);
            if (!$result) {
                throw new LocalizedException(__("Error changing remote path '%1': %2", $remote['path'], $sftp->getLastSFTPError()));
            }
        }

        return $sftp;
    }

    protected function _sftpDownload()
    {
        $this->activity(__('Downloading file from SFTP'));

        $conn = $this->_sftpOpen();
        $localFile = $this->getIo()->getFilepath($this->getFilename());
        @unlink($localFile);
        $result = $conn->get($this->getFilename(), $localFile);
        if (!$result) {
            throw new LocalizedException(__('Error transferring remote file: %1', $conn->getLastSFTPError()));
        }
        $conn->disconnect();
    }

    protected function _sftpUpload()
    {
        $this->activity(__('Uploading file to SFTP'));

        $conn = $this->_sftpOpen();
        $localFile = $this->getIo()->getFilepath($this->getFilename());
        $result = $conn->put($this->getFilename(), $localFile, SFTP::SOURCE_LOCAL_FILE);
        if (!$result) {
            throw new LocalizedException(__('Error transferring remote file: %1', $conn->getLastSFTPError()));
        }
        $conn->disconnect();
    }

    protected function _ftpOpen()
    {
        $remote = (array)$this->getData('options/remote');
        if (empty($remote['host'])) {
            throw new LocalizedException(__('Empty or invalid remote host name'));
        }
        $host = $remote['host'];
        $port = !empty($remote['port']) ? (int)$remote['port'] : 21;
        if ($remote['type'] === 'ftp') {
            $conn = @ftp_connect($host, $port);
        } else {
            $conn = @ftp_ssl_connect($host, $port);
        }
        if (!$conn) {
            $e = error_get_last();
            throw new LocalizedException(__("Error connecting to remote host '%1': %2", $remote['host'], $e['message']));
        }
        if (empty($remote['username']) && empty($remote['password'])) {
            $username = 'anonymous';
            $password = 'a@b.com';
        } elseif (empty($remote['username'])) {
            throw new LocalizedException(__('Empty or invalid remote user name'));
        } else {
            $username = $remote['username'];
            $password = !empty($remote['password']) ? $remote['password'] : '';
        }
        $result = @ftp_login($conn, $username, $password);
//        $result = @ftp_login($conn, $remote['username'], $remote['password']);
        if (!$result) {
            $e = error_get_last();
            throw new LocalizedException(__("Error logging in to remote host '%1': %2", $remote['host'], $e['message']));
        }

        if (!isset($remote['ftp_passive']) || $remote['ftp_passive']) {
            @ftp_pasv($conn, true);
        }

        if (!empty($remote['path'])) {
            $result = @ftp_chdir($conn, $remote['path']);
            if (!$result) {
                $e = error_get_last();
                throw new LocalizedException(__("Error changing remote path '%1': %2", $remote['path'], $e['message']));
            }
        }

        return $conn;
    }

    protected function _ftpDownload()
    {
        $remote = (array)$this->getData('options/remote');
        $this->activity(__('Downloading file from FTP'));

        $conn = $this->_ftpOpen();
        $localFile = $this->getIo()->getFilepath($this->getFilename());
        @unlink($localFile);
        $fileMode = isset($remote['ftp_file_mode']) ? $remote['ftp_file_mode'] : FTP_BINARY;
        $result = @ftp_get($conn, $localFile, $this->getFilename(), $fileMode);
        if (!$result) {
            $e = error_get_last();
            throw new LocalizedException(__('Error transferring remote file: %1', $e['message']));
        }
        @ftp_close($conn);
    }

    protected function _ftpUpload()
    {
        $remote = (array)$this->getData('options/remote');
        $this->activity(__('Uploading file to FTP'));

        $conn = $this->_ftpOpen();
        $localFile = $this->getIo()->getFilepath($this->getFilename());
        $fileMode = isset($remote['ftp_file_mode']) ? $remote['ftp_file_mode'] : FTP_BINARY;
        $result = @ftp_put($conn, $this->getFilename(), $localFile, $fileMode);
        if (!$result) {
            $e = error_get_last();
            throw new LocalizedException(__('Error transferring remote file: %1', $e['message']));
        }
        @ftp_close($conn);
    }

    protected function _zipArchive()
    {

    }

    protected function _zipExtract()
    {

    }

    protected function _processDir($dir, $default = true)
    {
        $dir = str_replace(
            ['{magento}', '{var}', '{media}'],
            [
                $this->_directoryList->getPath(DirectoryList::ROOT),
                $this->_directoryList->getPath(DirectoryList::VAR_DIR),
                $this->_directoryList->getPath(DirectoryList::MEDIA)
            ],
            $dir
        );
        return $dir;
    }

    public function getFileBaseDir()
    {
        $dir = $this->getBaseDir();
        if (!$dir) {
            $dir = $this->_scopeConfig->getValue(
                'urapidflow/dirs/' . $this->getProfileType() . '_dir',
                ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            );
        }
        return $this->_processDir($dir);
    }

    public function getImagesBaseDir($autoCreate = false)
    {
        $dir = $this->getData('options/dir/images');
        if (!$dir) {
            $dir = $this->_scopeConfig->getValue('urapidflow/dirs/images_dir', ScopeInterface::SCOPE_STORE,
                                                 $this->getStoreId());
        }
        $dir = $this->_processDir($dir);
        if (!$dir) {
            $dir = $this->_directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'import';
        } elseif ($dir[0] !== '/' && $dir[1] !== ':') {
            $dir = rtrim($this->getFileBaseDir(), '/') . '/' . $dir;
        }
        if ($autoCreate) {
            $this->_directoryWrite->create($dir)->create();
        }
        return $dir;
    }

    public function getLogBaseDir()
    {
        $dir = $this->_scopeConfig->getValue('urapidflow/dirs/log_dir', ScopeInterface::SCOPE_STORE,
                                             $this->getStoreId());
        return $this->_processDir($dir);
    }

    public function getLogFilename()
    {
        return $this->getFilename() . '-' . $this->getProfileType() . '.log';
    }

    public function getExcelReportBaseDir()
    {
        $dir = $this->_scopeConfig->getValue('urapidflow/dirs/report_dir', ScopeInterface::SCOPE_STORE,
                                             $this->getStoreId());
        return $this->_processDir($dir);
    }

    public function getExcelReportFilename($timeStamped = false)
    {
        $fileName = $this->getFilename();
        if ($timeStamped) {
            $fileName .= date('-m-d-Y_H-i-s');
        }
        $fileName .= '.xls';

        return $fileName;
    }

    public function exportExcelReport($alternative = false)
    {
        // open import file
        $this->ioOpenRead(false);
        // open log file
        $log = $this->getLogger()->start('r')->getIo();
        // start excel out file
        $out = $this->_ioFile
            ->setBaseDir($this->getExcelReportBaseDir())
            ->open($this->getExcelReportFilename($alternative), 'w');

        // excel report header
        $out->write(/** @lang XML */
            '<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
xmlns:html="http://www.w3.org/TR/REC-html40">
<Styles>
<Style ss:ID="Default" ss:Name="Normal"></Style>
<Style ss:ID="SUCCESS"><Interior ss:Color="#CEEAB0" ss:Pattern="Solid"/></Style>
<Style ss:ID="WARNING"><Interior ss:Color="#FDE9D9" ss:Pattern="Solid"/></Style>
<Style ss:ID="ERROR"><Interior ss:Color="#FAC090" ss:Pattern="Solid"/></Style>
</Styles>
<Worksheet ss:Name="Sheet1">');

        if (in_array($this->getDataType(), array('product', 'category'))) {
            $out->write(/** @lang XML */
                '
<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
<Selected/><FreezePanes/><FrozenNoSplit/><SplitHorizontal>1</SplitHorizontal>
<TopRowBottomPane>1</TopRowBottomPane><ActivePane>2</ActivePane>
<Panes><Pane><Number>3</Number></Pane><Pane><Number>2</Number><ActiveRow>0</ActiveRow></Pane></Panes>
</WorksheetOptions>');
        }

        $out->write('<Table>');

        $rowNum = 1;
        $row = true;
        $logRows = [];
        $lastLogRowNum = null;
        while (true) {
            if ($logRows) {
                $oldLogRows = $logRows;
                if ($lastLogRowNum) {
                    $logRows = array($lastLogRowNum => $oldLogRows[$lastLogRowNum]);
                } else {
                    $logRows = [];
                }
            } else {
                $logRows = [];
            }
            if ($log) {
                $lastLogRowNum = 0;
                for ($i = 0; $i < 1000; $i++) {
                    $l = $log->read();
                    if (!$l) {
                        $lastLogRowNum = false;
                        break;
                    }
                    if (sizeof($l) != 4) {
                        continue;
                    }
                    if (!(int)$l[1] || !(int)$l[2]) {
                        continue;
                    }
                    $logRows[$l[1]][$l[2] - 1][0] = $l[0];
                    $logRows[$l[1]][$l[2] - 1][1][] = $l[3];
                    $lastLogRowNum = $l[1];
                }
            }
            while (!empty($row) && (!$lastLogRowNum || $rowNum < $lastLogRowNum)) {
                $row = $this->ioRead();
                if (empty($row)) break 2;
                $logData = $rowNum == 1 ? true : (!empty($logRows[$rowNum]) ? $logRows[$rowNum] : array());
                $out->write($this->_getExcelRow($row, $logData));
                $rowNum++;
            }
        }
        $out->write('</Table></Worksheet></Workbook>');
        $out->close();

        return $this;
    }

    protected function _getExcelRow($r, $l = array())
    {
        if ($l === true) {
            $l = [
                0 => ['SUCCESS', __('Sample for searching')],
                1 => ['WARNING', __('Sample for searching')],
                2 => ['ERROR', __('Sample for searching')],
            ];
        }
        $out = '<Row>';
        foreach ($r as $k => $v) {
            $type = false;
            if (!empty($l[$k]) || !empty($l[-1])) {
                $type = !empty($l[$k][0]) ? $l[$k][0] : $l[-1][0];
                $toJoin = !empty($l[$k][1]) ? $l[$k][1] : (!empty($l[-1][1]) && $k == 0 ? $l[-1][1] : []);
                if($toJoin instanceof Phrase){
                    $toJoin = (string)$toJoin;
                }
                $comment = join("<br/>", (array)$toJoin);
                $out .= '<Cell ss:StyleID="' . $type . '">';
                if ($comment) {
                    $out .= '<Comment><ss:Data>' . __($type) . ': ' . htmlspecialchars($comment) . '</ss:Data></Comment>';
                }
            } elseif ($v !== '') {
                $out .= '<Cell>';
            } else {
                $out .= '<Cell/>';
            }
            if ($v !== '') {
                #$out .= '<Data ss:Type="String">'.($v!==''?'<![CDATA['.$v.']]>':'').'</Data></Cell>'."\n";
                $out .= '<Data ss:Type="String">' . htmlentities($v, ENT_QUOTES) . '</Data></Cell>';
            } elseif ($type) {
                $out .= '</Cell>';
            }
        }
        $out .= '</Row>' . "\n";
        return $out;
    }

    protected function _processColumnsPost()
    {
        if ($this->hasColumnsPost()) {
            $columns = [];
            foreach ($this->getColumnsPost() as $k => $a) {
                foreach ($a as $i => $v) {
                    if ($v !== '') {
                        $columns[$i][$k] = $v;
                    }
                }
            }
            $this->setColumns($columns);
        }
    }

    protected function _processPostData()
    {
        $this->_processColumnsPost();
        if ($this->hasRule()) {
            $this->getConditionsRule()->parseConditionsPost($this, $this->getRule());
        }
        if ($this->getJsonImport()) {
            $this->importFromJson($this->getJsonImport());
        }
    }

    protected function _serializeData()
    {
        foreach ($this->_jsonFields as $k => $f) {
            if (!is_null($this->getData($k))) {
                $this->setData($f, Json::encode($this->getData($k)));
            }
        }
    }

    protected function _unserializeData()
    {
        foreach ($this->_jsonFields as $k => $f) {
            if (!is_null($this->getData($f))) {
                $this->setData($k, Json::decode($this->getData($f), Json::TYPE_ARRAY));
            }
        }
    }

    protected function _applyDefaults()
    {
        foreach ($this->_defaults as $k => $d) {
            $this->setData($k, $this->_arrayMergeRecursive($d, (array)$this->getData($k)));
        }
    }

    public function importFromJson($json)
    {
        $data = Json::decode($json, Json::TYPE_ARRAY);
        if (!$data) {
            return $this;
        }
        foreach ($this->_jsonImportFields as $k) {
            if (empty($data[$k])) {
                continue;
            }
            $cur = $this->getData($k);
            $new = $data[$k];
            if (empty($cur) && is_array($new)) {
                $this->setData($k, $new);
            } elseif (is_array($cur) && is_array($new)) {
                $this->setData($k, $this->_arrayMergeRecursive($cur, $new));
            }
        }
        return $this;
    }

    public function exportToJson()
    {
        $data = $this->toArray($this->_jsonImportFields);
        foreach ($data as $k => $v) {
            if (!$v) {
                unset($data[$k]);
            }
        }

        $json = Json::encode($data);
        return $json;
    }

    public function _arrayMergeRecursive()
    {
        $params = func_get_args();
        $return = array_shift($params);
        foreach ($params as $array) {
            foreach ($array as $key => $value) {
                if (is_numeric($key) && (!in_array($value, $return))) {
                    if (is_array($value) && isset($return[$key])) {
                        $return[] = $this->_arrayMergeRecursive($return[$key], $value);
                    } else {
                        $return[] = $value;
                    }
                } else {
                    if (isset($return[$key]) && is_array($value) && is_array($return[$key])) {
                        $return[$key] = $this->_arrayMergeRecursive($return[$key], $value);
                    } else {
                        $return[$key] = $value;
                    }
                }
            }
        }

        return $return;
    }

    public function beforeSave()
    {
        $this->_processPostData();
        $this->_serializeData();
        parent::beforeSave();
        $this->_dataSaveAllowed = $this->_getData('title') && $this->_getData('profile_type');
    }

    protected function _afterLoad()
    {
        try {
            $this->_scopeConfig->getValue('urapidflow/dirs/log_dir', ScopeInterface::SCOPE_STORE, $this->getStoreId());
        } catch (\Exception $e) {
            $this->setStoreId(0);
        }
        $this->_unserializeData();
        $this->_applyDefaults();
        parent::_afterLoad();
    }

    protected $_defaultDatetimeFormat;

    public function getDefaultDatetimeFormat()
    {
        if (null === $this->_defaultDatetimeFormat) {
            $this->_localeResolver->emulate($this->getStoreId());
            $this->_defaultDatetimeFormat = $this->_localeDate
                ->getDateTimeFormat(\IntlDateFormatter::SHORT);
            $this->_localeResolver->revert();
            $this->_defaultDatetimeFormat = $this->_rapidFlowHelper
                ->convertIsoToPhpDateFormat($this->_defaultDatetimeFormat);
        }
        return $this->_defaultDatetimeFormat;
    }

    protected $_profileLocale;

    public function getProfileLocale()
    {
        if (null === $this->_profileLocale) {
            $this->_localeResolver->emulate($this->getStoreId());
            $this->_profileLocale = $this->_localeResolver->getLocale();
            $this->_localeResolver->revert();
        }
        return $this->_profileLocale;
    }

    public function getDataType()
    {
        return $this->getData("data_type");
    }

}
