<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Model\Import;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;

class Tracknumber extends \Magento\ImportExport\Model\Import\AbstractEntity
{
    const COL_SHIPMENT_ID = 'shipmentid';
    const COL_TRACKING_NUMBER = 'trackingnumber';
    const COL_CARRIER_CODE = 'carriercode';
    const COL_CARRIER_TITLE = 'title';

    const ERROR_COL_SHIPMENT_ID_IS_EMPRY = 'shipmentIdEmpty';
    const ERROR_COL_SHIPMENT_ID_NOT_FOUND = 'shipmentNotFound';
    const ERROR_COL_TRACKING_NUMBER_IS_EMPRY = 'trackingNumberEmpty';
    const ERROR_COL_CARRIER_CODE_IS_EMPRY = 'carrierCodeEmpty';
    const ERROR_COL_CARRIER_TITLE_IS_EMPRY = 'carrierTitleEmpty';
    const ALLOWED_ERROR_LIMIT = 'isErrorLimit';

    /**
     * @var array
     */
    protected $validColumnNames = [
        self::COL_SHIPMENT_ID,
        self::COL_TRACKING_NUMBER,
        self::COL_CARRIER_CODE,
        self::COL_CARRIER_TITLE
    ];

    /**
     * @var array
     */
    private $messageTemplates = [
        self::ERROR_COL_SHIPMENT_ID_IS_EMPRY => '<b>Error!</b> Shipment Id Field Is Empty',
        self::ERROR_COL_SHIPMENT_ID_NOT_FOUND=> '<b>Error!</b> Shipment Id doesn\'t exists',
        self::ERROR_COL_TRACKING_NUMBER_IS_EMPRY => '<b>Error!</b> Tracking number Field Is Empty',
        self::ERROR_COL_CARRIER_CODE_IS_EMPRY => '<b>Error!</b> Carrier code Field Is Empty',
        self::ERROR_COL_CARRIER_TITLE_IS_EMPRY => 'Warning! Carrier title Field Is Empty',
        self::ALLOWED_ERROR_LIMIT => '<b>Allowed errors limit is reached.</b>'
    ];

    protected $masterAttributeCode = self::COL_SHIPMENT_ID;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentRepository
     */
    private $shipmentRepository;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    private $trackFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track
     */
    private $trackResource;

    /**
     * @var bool
     */
    private $isImport = false;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory
     */
    private $trackCollectionFactory;

    /**
     * Tracknumber constructor.
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track $trackResource
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\ImportExport\Model\ImportFactory $importFactory,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Sales\Model\Order\ShipmentRepository $shipmentRepository,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track $trackResource,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        array $data = []
    ) {
        $this->errorMessageTemplates = array_merge($this->errorMessageTemplates, $this->messageTemplates);
        $this->shipmentRepository = $shipmentRepository;
        $this->trackFactory = $trackFactory;
        $this->trackResource = $trackResource;
        $this->trackCollectionFactory = $trackCollectionFactory;
        $this->needColumnCheck = true;

        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $data
        );
    }

    /**
     * Not empty column validation
     *
     * @param array $rowData
     * @param int $rowNum
     * @param string $column
     * @param string $errorCode
     * @param string $errorLevel
     */
    public function validateNotEmpty(
        array $rowData,
        $rowNum,
        $column,
        $errorCode,
        $errorLevel = ProcessingError::ERROR_LEVEL_NOT_CRITICAL
    ) {
        /**
         * Error level import fix.
         * Less then ProcessingError::ERROR_LEVEL_CRITICAL will pass validation
         */
        if ($this->isImport && $errorLevel == ProcessingError::ERROR_LEVEL_NOT_CRITICAL) {
            $errorLevel = ProcessingError::ERROR_LEVEL_CRITICAL;
        }

        if (!isset($rowData[$column]) || $rowData[$column] == "") {
            $this->addRowError($errorCode, $rowNum, null, null, $errorLevel);
        }
    }

    /**
     * Validation failure message template definitions
     *
     * @var array $rowData
     * @var int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        /**
         * Import logic fix.
         * hasToBeTerminated doesn't check while validation
         */
        if (!$this->isImport && $this->getErrorAggregator()->hasToBeTerminated()) {
            $this->addRowError(self::ALLOWED_ERROR_LIMIT, 0, null, null, ProcessingError::ERROR_LEVEL_CRITICAL);
            return true;
        }

        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;
        $this->_processedEntitiesCount++;
        $this->validateNotEmpty($rowData, $rowNum, self::COL_SHIPMENT_ID, self::ERROR_COL_SHIPMENT_ID_IS_EMPRY);
        $shipmentId = $rowData[self::COL_SHIPMENT_ID];
        try {
            $this->shipmentRepository->get((int) $shipmentId);
        } catch (NoSuchEntityException $e) {
            $this->addRowError(
                self::ERROR_COL_SHIPMENT_ID_NOT_FOUND,
                $rowNum,
                null,
                null,
                ProcessingError::ERROR_LEVEL_NOT_CRITICAL
            );
        }
        $this->validateNotEmpty($rowData, $rowNum, self::COL_TRACKING_NUMBER, self::ERROR_COL_TRACKING_NUMBER_IS_EMPRY);
        $this->validateNotEmpty($rowData, $rowNum, self::COL_CARRIER_CODE, self::ERROR_COL_CARRIER_CODE_IS_EMPRY);
        $this->validateNotEmpty(
            $rowData,
            $rowNum,
            self::COL_CARRIER_TITLE,
            self::ERROR_COL_CARRIER_TITLE_IS_EMPRY,
            ProcessingError::ERROR_LEVEL_WARNING
        );
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * @return bool
     */
    protected function _importData()
    {
        $this->processTracknumbers();
        return true;
    }

    /**
     * Prepare track numbers for saving
     *
     */
    private function processTracknumbers()
    {
        /**
         * Import fix. Errors less then ProcessingError::ERROR_LEVEL_CRITICAL validateRow as true.
         * Just skip then because Import button is active.
         */
        $this->isImport = true;

        $behavior = $this->getBehavior();
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $tracknumbers = [];
            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                $tracknumbers[$rowData[self::COL_SHIPMENT_ID]][] = $rowData;
            }
            switch ($behavior) {
                case \Magento\ImportExport\Model\Import::BEHAVIOR_ADD_UPDATE:
                    $this->saveTracknumbers($tracknumbers);
                    break;
                case \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE:
                    $this->deleteTracknumbers($tracknumbers);
                    break;
            }
        }
        /** Import logic fix. Clear error log after import */
        $this->getErrorAggregator()->clear();
    }

    /**
     * Save track numbers
     *
     * @param array $tracknumbers
     */
    private function saveTracknumbers(array $tracknumbers)
    {
        foreach ($tracknumbers as $shipmentId => $tracks) {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment = $this->shipmentRepository->get((int) $shipmentId);
            foreach ($tracks as $data) {
                $title = !empty($data[self::COL_CARRIER_TITLE])?$data[self::COL_CARRIER_TITLE]:['null' => true];
                $trackExists = $this->trackCollectionFactory->create()
                    ->addFieldToFilter('parent_id', $shipment->getId())
                    ->addFieldToFilter('track_number', $data[self::COL_TRACKING_NUMBER])
                    ->addFieldToFilter('carrier_code', $data[self::COL_CARRIER_CODE])
                    ->addFieldToFilter('title', $title)
                    ->getSize();
                if (!$trackExists) {
                    /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
                    $track = $this->trackFactory->create();
                    $track
                        ->setShipment($shipment)
                        ->setParentId($shipment->getId())
                        ->setOrderId($shipment->getOrderId())
                        ->setNumber($data[self::COL_TRACKING_NUMBER])
                        ->setCarrierCode($data[self::COL_CARRIER_CODE])
                        ->setTitle($data[self::COL_CARRIER_TITLE]);
                    $this->trackResource->save($track);
                }
            }
        }
    }

    /**
     * Delete track numbers
     *
     * @param array $tracknumbers
     */
    private function deleteTracknumbers(array $tracknumbers)
    {
        foreach ($tracknumbers as $shipmentId => $tracks) {
            foreach ($tracks as $data) {
                $title = !empty($data[self::COL_CARRIER_TITLE])?$data[self::COL_CARRIER_TITLE]:['null' => true];
                $tracksToDelete = $this->trackCollectionFactory->create()
                    ->addFieldToFilter('parent_id', $shipmentId)
                    ->addFieldToFilter('track_number', $data[self::COL_TRACKING_NUMBER])
                    ->addFieldToFilter('carrier_code', $data[self::COL_CARRIER_CODE])
                    ->addFieldToFilter('title', $title)
                    ->load();
                foreach ($tracksToDelete->getItems() as $track) {
                    $this->trackResource->delete($track);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'amasty_oaction';
    }
}
