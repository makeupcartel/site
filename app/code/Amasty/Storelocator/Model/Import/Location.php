<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Import;

use Amasty\Storelocator\Model\Import\Validator\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;

class Location extends AbstractEntity
{
    const VALUE_ALL_WEBSITES = 'All Websites';

    const COL_ID = 'id';

    const COL_NAME = 'name';

    const COL_COUNTRY = 'country';

    const COL_CITY = 'city';

    const COL_ZIP = 'zip';

    const COL_ADDRESS = 'address';

    const COL_STATE = 'state';

    const COL_DESCRIPTION = 'description';

    const COL_PHONE = 'phone';

    const COL_EMAIL = 'email';

    const COL_STORES = 'stores';

    const COL_STATUS = 'status';

    const COL_PHOTO = 'store_img';

    const MARKER = 'marker_img';

    const COL_WEBSITE = 'website';

    const COL_LAT = 'lat';

    const COL_LNG = 'lng';

    const COL_MONDAY = 'monday';

    const COL_TUESDAY = 'tuesday';

    const COL_WEDNESDAY = 'wednesday';

    const COL_THURSDAY = 'thursday';

    const COL_FRIDAY = 'friday';

    const COL_SATURDAY = 'saturday';

    const COL_SUNDAY = 'sunday';

    const COL_POSITION = 'position';

    const COL_SHOW_SCHEDULE = 'show_schedule';

    const VALIDATOR_MAIN = 'validator';

    const VALIDATOR_COUNTRY = 'country';

    const VALIDATOR_PHOTO = 'photo';

    const TABLE_AMASTY_LOCATION = 'amasty_amlocator_location';

    const TABLE_AMASTY_STORE_ATTRIBUTE = 'amasty_amlocator_store_attribute';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        ValidatorInterface::ERROR_INVALID_PHOTO            => 'Invalid Photo or Marker, please check if file exists in the system',
        ValidatorInterface::ERROR_COUNTRY_IS_EMPTY         => 'Invalid Country',
        ValidatorInterface::ERROR_ID_IS_EMPTY              => 'Id Field Is Empty',
        ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE => 'can\'t access to Media Url',
        ValidatorInterface::ERROR_NAME_IS_EMPTY            => 'Name Is Empty'
    ];

    /**
     * If we should check column names
     *
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * Columns for locations
     *
     * @var array
     */
    protected $locationColumnNames = [
        self::COL_ID,
        self::COL_NAME,
        self::COL_COUNTRY,
        self::COL_CITY,
        self::COL_ZIP,
        self::COL_ADDRESS,
        self::COL_STATE,
        self::COL_DESCRIPTION,
        self::COL_PHONE,
        self::COL_EMAIL,
        self::COL_STORES,
        self::COL_STATUS,
        self::COL_PHOTO,
        self::MARKER,
        self::COL_WEBSITE,
        self::COL_LAT,
        self::COL_LNG,
        self::COL_POSITION,
        self::COL_SHOW_SCHEDULE
    ];

    /**
     * Columns for schedule
     *
     * @var array
     */
    protected $scheduleColumnNames = [
        self::COL_MONDAY,
        self::COL_TUESDAY,
        self::COL_WEDNESDAY,
        self::COL_THURSDAY,
        self::COL_FRIDAY,
        self::COL_SATURDAY,
        self::COL_SUNDAY
    ];

    /**
     * Valid column names
     *
     * @var array
     */
    protected $validColumnNames = [];

    protected $coordinatesFields = [
        self::COL_COUNTRY,
        self::COL_CITY,
        self::COL_STATE,
        self::COL_ZIP,
        self::COL_ADDRESS
    ];

    /**
     * @var \Amasty\Storelocator\Model\Import\Proxy\Location\ResourceModelFactory
     */
    protected $_resourceFactory;

    /**
     * Media files uploader
     *
     * @var \Magento\CatalogImportExport\Model\Import\Uploader
     */
    protected $_fileUploader;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\UploaderFactory
     */
    protected $_uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var array
     */
    protected $_validators = [];

    /**
     * Column names that holds images files names
     *
     * @var string[]
     */
    protected $_imagesArrayKeys = ['photo'];

    /**
     * @var \Magento\Framework\Filesystem\File\ReadFactory
     */
    private $readFactory;

    /**
     * @var \Magento\Framework\HTTP\Adapter\CurlFactory
     */
    private $curlFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection
     */
    private $attributeCollection;

    /**
     * @var \Amasty\Storelocator\Model\LocationFactory
     */
    private $locationFactory;

    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    private $dataHelper;

    /**
     * @var Json
     */
    private $serializer;

    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\File\ReadFactory $readFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Proxy\Location\ResourceModelFactory $resourceModelFactory,
        Validator\Country $validatorCountry,
        Validator\Photo $validatorPhoto,
        \Amasty\Storelocator\Model\ResourceModel\Attribute\CollectionFactory $attributeCollectionFactory,
        \Amasty\Storelocator\Model\LocationFactory $locationFactory,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        Validator $validator
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_uploaderFactory = $uploaderFactory;
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->_connection = $resource->getConnection('write');
        $this->_validators[self::VALIDATOR_MAIN] = $validator->init($this);
        //add validators for Country and Image
        $this->_validators[self::VALIDATOR_PHOTO] = $validatorPhoto;
        $this->_validators[self::VALIDATOR_COUNTRY] = $validatorCountry;
        $this->errorAggregator = $errorAggregator;
        foreach (array_merge($this->errorMessageTemplates, $this->_messageTemplates) as $errorCode => $message) {
            $this->getErrorAggregator()->addErrorMessageTemplate($errorCode, $message);
        }
        $this->scopeConfig = $scopeConfig;
        $this->readFactory = $readFactory;
        $this->curlFactory = $curlFactory;
        $this->_resourceFactory = $resourceModelFactory;
        $this->attributeCollection = $attributeCollectionFactory->create();
        $this->locationFactory = $locationFactory;
        $this->dataHelper = $dataHelper;
        $this->initLocatorColumns();
    }


    protected function _getValidator($type)
    {
        return $this->_validators[$type];
    }

    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'amasty_storelocator';
    }

    /**
     * Google maps api key getter.
     *
     * @return string
     */
    public function getGoogleMapsApiKey()
    {
        $apiKey = $this->scopeConfig->getValue(
            'amlocator/locator/api',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $apiKey;
    }

    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int   $rowNum
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;
        // BEHAVIOR_DELETE use specific validation logic
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (!isset($rowData[self::COL_ID])) {
                $this->addRowError(ValidatorInterface::ERROR_ID_IS_EMPTY, $rowNum);
                return false;
            }
            return true;
        }

        $this->_processedEntitiesCount++;

        if (!$this->_getValidator(self::VALIDATOR_MAIN)->isValid($rowData)) {
            foreach ($this->_getValidator(self::VALIDATOR_MAIN)->getMessages() as $message) {
                $this->addRowError($message, $rowNum);
            }
        }

        if (!$this->_getValidator(self::VALIDATOR_COUNTRY)->isValid($rowData)) {
            foreach ($this->_getValidator(self::VALIDATOR_MAIN)->getMessages() as $message) {
                $this->addRowError($message, $rowNum);
            }
        }

        foreach ($rowData as $value) {
            if (!mb_check_encoding($value)) {
                $this->addRowError(ValidatorInterface::ENCODING_ERROR, $rowNum);
            }
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Validate data rows and save bunches to DB
     *
     * @return $this
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $currentDataSize = 0;
        $bunchRows = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $maxDataSize = $this->_resourceHelper->getMaxDataSize();
        $bunchSize = $this->_importExportData->getBunchSize();
        $idSet = [];

        $source->rewind();
        $this->_dataSourceModel->cleanBunches();

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->_dataSourceModel->saveBunch($this->getEntityTypeCode(), $this->getBehavior(), $bunchRows);

                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen($this->getSerializer()->serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
            }
            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                    $idSet[$rowData['id']] = true;
                } catch (\InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                $this->_processedRowsCount++;

                if ($this->validateRow($rowData, $source->key())) {
                    // add row to bunch for save
                    $rowData = $this->_prepareRowForDb($rowData);
                    $rowSize = strlen($this->jsonHelper->jsonEncode($rowData));

                    $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;

                    if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = [$source->key() => $rowData];
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $currentDataSize += $rowSize;
                    }
                }
                $source->next();
            }
        }
        $this->_processedEntitiesCount = count($idSet);

        return $this;
    }

    /**
     * @return Json
     */
    private function getSerializer()
    {
        if (null === $this->serializer) {
            $this->serializer = ObjectManager::getInstance()->get(Json::class);
        }
        return $this->serializer;
    }

    /**
     * Returns an object for upload a media files
     *
     * @return \Magento\CatalogImportExport\Model\Import\Uploader
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getUploader()
    {
        if (is_null($this->_fileUploader)) {
            $this->_fileUploader = $this->_uploaderFactory->create();

            $this->_fileUploader->init();

            $dirConfig = DirectoryList::getDefaultConfig();
            $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];

            $DS = DIRECTORY_SEPARATOR;

            if (!empty($this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR])) {
                $tmpPath = $this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR];
            } else {
                $tmpPath = $dirAddon . $DS . $this->_mediaDirectory->getRelativePath('import');
            }

            if (!$this->_fileUploader->setTmpDir($tmpPath)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File directory \'%1\' is not readable.', $tmpPath)
                );
            }
            $destinationDir = "amasty/amlocator";
            $destinationPath = $dirAddon . $DS . $this->_mediaDirectory->getRelativePath($destinationDir);

            $this->_mediaDirectory->create($destinationPath);
            if (!$this->_fileUploader->setDestDir($destinationPath)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File directory \'%1\' is not writable.', $destinationPath)
                );
            }
        }
        return $this->_fileUploader;
    }

    /**
     * Uploading files into the "amasty/amlocator media folder.
     * Return a new file name if the same file is already exists.
     *
     * @param string $fileName
     * @return string
     */
    protected function uploadMediaFiles($fileName, $renameFileOff = false)
    {
        try {
            $res = $this->getUploader()->move($fileName, $renameFileOff);
            return $res['file'];
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function _importData()
    {
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteLocation();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->saveAndReplaceLocations();
        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            $this->saveAndReplaceLocations();
        }

        return true;
    }

    /**
     * Deletes Advanced price data from raw data.
     *
     * @return $this
     */
    public function deleteLocation()
    {
        $listIds = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);
                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $listIds[] = $rowData[self::COL_ID];
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }
        $tableName = $this->_resourceFactory->create()->getTable(self::TABLE_AMASTY_LOCATION);
        try {
            $this->countItemsDeleted += $this->_connection->delete(
                $tableName,
                $this->_connection->quoteInto(self::COL_ID . ' IN (?)', $listIds)
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Save and replace Locations
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceLocations()
    {
        $behavior = $this->getBehavior();
        if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
            $this->_cachedSkuToDelete = null;
        }
        $locationsRows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $locations = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$locationsRows) {
                    foreach ($rowData as $key => $value) {
                        $locationsRows[] = $key;
                    }
                }
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addRowError(ValidatorInterface::ERROR_ID_IS_EMPTY, $rowNum);
                    continue;
                }
                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                }
                if (!isset($rowData[self::COL_LAT]) || !isset($rowData[self::COL_LNG])) {
                    $locationInfo = [];
                    foreach ($this->coordinatesFields as $coordinatesField) {
                        if (isset($rowData[$coordinatesField])) {
                            $locationInfo[] = $rowData[$coordinatesField];
                        }
                    }
                    $coordinates = $this->getCoordinates(implode(' ', $locationInfo));
                    if (is_array($coordinates)) {
                        $locationsRows[] = 'lat';
                        $locationsRows[] = 'lng';
                    }
                }

                foreach ($locationsRows as $key) {
                    switch ($key) {
                        case 'country':
                            $locations[$rowNum][$key] = $this->checkCountry($rowData[$key]);
                            break;

                        case 'store_img':
                            $uploadedFile = '';
                            if (!empty(trim($rowData[$key]))) {
                                $uploadedFile = $this->uploadMediaFiles(trim($rowData[$key]), true);
                                if (!$uploadedFile) {
                                    $this->addRowError(
                                        ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE,
                                        $rowNum,
                                        null,
                                        null,
                                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                                    );
                                }
                            }
                            $locations[$rowNum][$key] = $uploadedFile;
                            break;

                        case 'marker_img':
                            $uploadedMarker = '';
                            if (!empty(trim($rowData[$key]))) {
                                $uploadedMarker = $this->uploadMediaFiles(trim($rowData[$key]), true);
                                if (!$uploadedMarker) {
                                    $this->addRowError(
                                        ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE,
                                        $rowNum,
                                        null,
                                        null,
                                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                                    );
                                }
                            }
                            $locations[$rowNum][$key] = $uploadedMarker;
                            break;

                        case 'lat':
                            if (isset($rowData[$key])) {
                                $locations[$rowNum][$key] = $rowData[$key];
                            } else {
                                $locations[$rowNum][$key] = $coordinates[$key];
                            }
                            break;

                        case 'lng':
                            if (isset($rowData[$key])) {
                                $locations[$rowNum][$key] = $rowData[$key];
                            } else {
                                $locations[$rowNum][$key] = $coordinates[$key];
                            }
                            break;

                        case 'stores':
                            if (isset($rowData[$key])) {
                                if ($rowData[$key] == 'all') {
                                    $locations[$rowNum][$key] = ',0,';
                                } else {
                                    $locations[$rowNum][$key] = ',' . $rowData[$key] . ',';
                                }
                            }
                            break;

                        default:
                            $locations[$rowNum][$key] = $rowData[$key];
                            break;
                    }
                }
            }
            $this->saveLocation($locations, self::TABLE_AMASTY_LOCATION);
        }

        return $this;
    }

    /**
     * Save product prices.
     *
     * @param array  $locations
     * @param string $table
     *
     * @return $this
     */
    protected function saveLocation(array $locations, $table)
    {
        $tableName = $this->_resourceFactory->create()->getTable($table);
        $delLocationIds = [];
        foreach ($locations as $location) {
            if (isset($location[self::COL_ID])) {
                $delLocationIds[] = $location[self::COL_ID];
            }
        }
        if (Import::BEHAVIOR_APPEND != $this->getBehavior()) {
            $this->_connection->delete(
                $tableName,
                $this->_connection->quoteInto('id IN (?)', $delLocationIds)
            );
        }

        foreach ($locations as $location) {
            $locationModel = $this->locationFactory->create();
            if (!empty($location[self::COL_ID])) {
                $locationModel->load($location[self::COL_ID]);
                if (!$locationModel->getId()) {
                    $locationModel->setModelFlags();
                }
            }
            $locationData = $this->prepareData($location);
            $locationModel->addData($locationData);
            $locationModel->save();
        }

        return $this;
    }

    protected function checkCountry($country)
    {
        /** @var \Amasty\Storelocator\Model\Import\Validator\Country $validator */
        $validator = $this->_getValidator(self::VALIDATOR_COUNTRY);
        if (strlen($country) > 2) {
            $country = $validator->getCountryByName($country);
        }
        return $country;
    }

    public function getCoordinates($address)
    {
        $apiKey = $this->getGoogleMapsApiKey();
        $query = [
            'sensor'  => 'false',
            'address' => $address,
            'key'     => $apiKey
        ];

        $url = "https://maps.google.com/maps/api/geocode/json?" . http_build_query($query);
        $httpAdapter = $this->curlFactory->create();
        $httpAdapter->write(\Zend_Http_Client::GET, $url, '1.1', ['Connection: close']);
        $response = $httpAdapter->read();
        $body = \Zend_Http_Response::extractBody($response);
        //generate array object from the response from the web

        $location = ['lat' => 0, 'lng' => 0];
        if ($body) {
            $json = $this->jsonHelper->jsonDecode($body);
            if ($json['status'] == 'OK' && isset($json['results'][0]['geometry']['location'])) {
                $location = [
                    'lat' => $json['results'][0]['geometry']['location']['lat'],
                    'lng' => $json['results'][0]['geometry']['location']['lng']
                ];
            }
        }

        return $location;
    }

    /**
     * Initialize locator attributes
     */
    private function initLocatorColumns()
    {
        $this->validColumnNames = array_merge($this->locationColumnNames, $this->scheduleColumnNames);
        foreach ($this->attributeCollection as $attribute) {
            $this->validColumnNames[] = $attribute->getAttributeCode();
        }
    }

    /**
     * Prepare data for saving
     *
     * @param array $locationData
     *
     * @return array $locationData
     */
    private function prepareData($locationData)
    {
        $attributeCodes = $this->attributeCollection->getColumnValues('attribute_code');
        foreach ($locationData as $key => $value) {
            if (in_array($key, $attributeCodes)) {
                $attributeId = $this->attributeCollection
                    ->getItemByColumnValue('attribute_code', $key)
                    ->getAttributeId();
                $locationData['store_attribute'][$attributeId] = $value;
            } elseif (in_array($key, $this->scheduleColumnNames)) {
                $defaultTime = [
                    '0' => '00',    // default hours
                    '1'   => '00'   // default minutes
                ];
                $locationData['schedule'][$key]['from'] = $locationData['schedule'][$key]['to'] = $defaultTime;

                $dayTimes = explode('-', $value);
                if (isset($dayTimes[0], $dayTimes[1])) {
                    $allDays = array_merge(explode(':', $dayTimes[0]), explode(':', $dayTimes[1]));
                    if (count($allDays) == 4) {
                        $locationData['schedule'][$key]['from'][0] = $allDays[0];
                        $locationData['schedule'][$key]['from'][1] = $allDays[1];
                        $locationData['schedule'][$key]['to'][0] = $allDays[2];
                        $locationData['schedule'][$key]['to'][1] = $allDays[3];
                    }
                }
            }
        }

        return $locationData;
    }
}
