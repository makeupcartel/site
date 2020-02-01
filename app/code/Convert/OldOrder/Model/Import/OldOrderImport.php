<?php
namespace Convert\OldOrder\Model\Import;

use Convert\OldOrder\Model\Import\OldOrderImport\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\App\ResourceConnection;

class OldOrderImport extends \Magento\ImportExport\Model\Import\Entity\AbstractEntity
{
    const ID = 'id';
    const STORE_ID = 'store_id';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const EMAIL_ADDRESS = 'email_address';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const SUBTOTAL = 'subtotal';
    const TAX = 'tax';
    const SHIPPING = 'shipping';
    const DISCOUNT = 'discount';
    const GRAND_TOTAL = 'grand_total';
    const STATUS = 'status';
    const PAYMENT_METHOD_NAME = 'payment_method_name';
    const SHIPPING_METHOD_NAME = 'shipping_method_name';
    const MESSAGE = 'message_deltail';
    const DATE = 'ordered_date';
    const TABLE_Entity = 'old_order';

    // [0] => firstname
    // [1] => lastname
    // [2] => email_address
    // [3] => is_guest_order
    // [4] => order_increment_id
    // [5] => shipping_total
    // [6] => order_total
    // [7] => order_tax
    // [8] => discount_amount
    // [9] => payment_method_name
    // [10] => shipping_method_name
    // [11] => ordered_date


    /**
     * Validation failure message template definitions
     * @var array
     */
    protected $_messageTemplates = [
    ValidatorInterface::ERROR_MESSAGE_IS_EMPTY => 'Order is empty',
    ];
     protected $_permanentAttributes = [self::ORDER_INCREMENT_ID];
    /**
     * If we should check column names
     * @var bool
     */
    protected $needColumnCheck = true;
    /**
     * Valid column names
     * @array
     */
    protected $validColumnNames = [

    self::FIRSTNAME,
    self::LASTNAME,
    self::EMAIL_ADDRESS,
    'is_guest_order',
    self::ORDER_INCREMENT_ID,
    'shipping_total',
    'order_total',
    'order_tax',
    'discount_amount',
    self::PAYMENT_METHOD_NAME,
    self::SHIPPING_METHOD_NAME,
    self::DATE,
    ];


    protected $listOrderId = [];

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;
    protected $_validators = [];
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_connection;
    protected $_resource;
    /**
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */


   

    public function __construct(
    \Magento\Framework\Json\Helper\Data $jsonHelper,
    \Magento\ImportExport\Helper\Data $importExportData,
    \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
    \Magento\Framework\App\ResourceConnection $resource,
    \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
    \Magento\Framework\Stdlib\StringUtils $string,
    ProcessingErrorAggregatorInterface $errorAggregator
    ) {
    $this->jsonHelper = $jsonHelper;
    $this->_importExportData = $importExportData;
    $this->_resourceHelper = $resourceHelper;
    $this->_dataSourceModel = $importData;
    $this->_resource = $resource;
    $this->_connection = $resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
    $this->errorAggregator = $errorAggregator;
    $this->listOrderId = $this->getOrdersId();
    }
    /**
     * Get list order_increment_id in table old_order 
     *
     * @return array
     */
    public function getOrdersId(){
        $result = array();
        if(!$this->listOrderId || count($this->listOrderId) < 1){
            $select = $this->_connection->select()->from(['ce'=>'old_order'],['order_increment_id']);
            $result = $this->_connection->fetchCol($select);
        }
        return $result;
    }
    public function getValidColumnNames()
    {
        return $this->validColumnNames;
    }
    /**
     * Entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'old_order';
    }
    /**
     * Row validation.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return bool
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $title = false;
        if (isset($this->_validatedRows[$rowNum])) {
            return !$this->getErrorAggregator()->isRowInvalid($rowNum);
        }
        $this->_validatedRows[$rowNum] = true;
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }
    /**
     * Create Advanced old order data from raw data.
     *
     * @throws \Exception
     * @return bool Result of operation.
     */
    protected function _importData()
    {
        $this->saveEntity();
        return true;
    }
    /**
     * Save Order
     *
     * @return $this
     */
    public function saveEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }
    /**
     * Replace Order
     *
     * @return $this
     */
    public function replaceEntity()
    {
        $this->saveAndReplaceEntity();
        return $this;
    }
    /**
     * Save and replace data order
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveAndReplaceEntity()
    {
        $i = 0;
	    $behavior = $this->getBehavior();
	    $listID = [];
	    // $bunch = $this->_dataSourceModel->getNextBunch();
	    // var_dump(count($bunch));die;

        // echo "<pre>";
        //     print_r($this->listOrderId);
        // echo "</pre>";die;
	    while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            // echo 'run '.$i++;
	        $entityList = [];
            $entityAddList = [];
            $entityUpdateList = [];
        
            $listOrderIncrementId = $this->listOrderId;
	        foreach ($bunch as $rowNum => $rowData) {
	            if (!$this->validateRow($rowData, $rowNum)) {
	                $this->addRowError(ValidatorInterface::ERROR_TITLE_IS_EMPTY, $rowNum);
	                continue;
	            }
	            if ($this->getErrorAggregator()->hasToBeTerminated()) {
	                $this->getErrorAggregator()->addRowToSkip($rowNum);
	                continue;
	            }
	            $rowId= $rowData[self::ORDER_INCREMENT_ID];

		        if(!in_array($rowId, $listID)){
		            $listID[]=$rowId;	

		            $entityList[$rowId] = [
		                // self::ID => $rowData[self::ORDER_INCREMENT_ID],
		                self::STORE_ID => '1',
		                self::FIRSTNAME => $rowData[self::FIRSTNAME],
		                self::LASTNAME => $rowData[self::LASTNAME],
		                self::EMAIL_ADDRESS => $rowData[self::EMAIL_ADDRESS],
		                self::ORDER_INCREMENT_ID => $rowData[self::ORDER_INCREMENT_ID],
		                // self::SUBTOTAL,
		                self::TAX => $rowData['order_tax'],
		                self::SHIPPING => $rowData['shipping_total'],
		                self::DISCOUNT => $rowData['discount_amount'],
		                self::GRAND_TOTAL => $rowData['order_total'],
		                self::PAYMENT_METHOD_NAME => $rowData[self::PAYMENT_METHOD_NAME],
		                self::SHIPPING_METHOD_NAME => $rowData[self::SHIPPING_METHOD_NAME],
		                self::DATE => $rowData[self::DATE],
		            ];

                    if(in_array($rowId, $this->listOrderId)){
                        $entityUpdateList[$rowId] = $entityList[$rowId];
                    }else{
                        $entityAddList[$rowId] = $entityList[$rowId];
                    }

                    
		        }
	        }

	        if (\Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE == $behavior) {
	            if ($listID) {
	                if ($this->deleteEntityFinish(array_unique($listID), self::TABLE_Entity)) {
	                    $this->saveEntityFinish($entityList, self::TABLE_Entity);
	                }
	            }
	        } elseif (\Magento\ImportExport\Model\Import::BEHAVIOR_APPEND == $behavior) {
	            $this->saveEntityFinish($entityAddList, self::TABLE_Entity);
                $this->updateEntityFinish($entityUpdateList, self::TABLE_Entity);
	        }
	    }
        return $this;
    }
    /**
     * Save old order to old_order.
     *
     * @param array $entityData
     * @param string $table
     */
    protected function saveEntityFinish(array $entityData, $table)
    {
	    if ($entityData) {
	        $tableName = $this->_connection->getTableName($table);
	        $this->_connection->insertMultiple($tableName, $entityData);
	    }
    }

    /**
     * Update old order to old_order.
     *
     * @param array $entityData
     * @param string $table
     */
    protected function updateEntityFinish(array $entityData, $table)
    {
        if ($entityData) {
            $tableName = $this->_connection->getTableName($table);
            foreach($entityData as $key => $value){
                $this->_connection->update($tableName, $value,['order_increment_id = ?' => $key]);
            }
        }
    }

    protected function deleteEntityFinish(array $listID, $table)
    {
        if ($table && $listID) {
            try {
                $this->countItemsDeleted += $this->_connection->delete(
                    $this->_connection->getTableName($table),
                    $this->_connection->quoteInto('order_increment_id IN (?)', $listID)
                );
                return true;
            } catch (\Exception $e) {
                return false;
            }

        } else {
            return false;
        }
    }
}

