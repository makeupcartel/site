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
 * @package    \Unirgy\CatalogTest
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product;

use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Unirgy\RapidFlow\Helper\Data as HelperData;

/**
 * Class Collection
 * @package Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product
 */
class Collection
    extends ProductCollection
{
    /**
     * @var HelperData
     */
    protected $_rapidFlowHelper;

    /**
     * @var Session
     */
    protected $_customerModelSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeModelStoreManagerInterface;

    /**
     * Collection constructor.
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param EavEntityFactory $eavEntityFactory
     * @param Helper $resourceHelper
     * @param UniversalFactory $universalFactory
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param State $catalogProductFlatState
     * @param ScopeConfigInterface $scopeConfig
     * @param OptionFactory $productOptionFactory
     * @param Url $catalogUrl
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param GroupManagementInterface $groupManagement
     * @param AdapterInterface $connection
     * @param HelperData $rapidFlowHelper
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        EavEntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        StoreManagerInterface $storeManager,
        Manager $moduleManager,
        State $catalogProductFlatState,
        ScopeConfigInterface $scopeConfig,
        OptionFactory $productOptionFactory,
        Url $catalogUrl,
        TimezoneInterface $localeDate,
        Session $customerSession,
        DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        HelperData $rapidFlowHelper,
        AdapterInterface $connection = null
    ) {
        $this->_rapidFlowHelper = $rapidFlowHelper;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $eavConfig, $resource,
                            $eavEntityFactory, $resourceHelper, $universalFactory, $storeManager, $moduleManager,
                            $catalogProductFlatState, $scopeConfig, $productOptionFactory, $catalogUrl, $localeDate,
                            $customerSession, $dateTime, $groupManagement, $connection);
    }

    /**
     * @param $attributeCode
     * @param string $joinType
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addAttributeToJoin($attributeCode, $joinType = 'inner')
    {
        if (isset($this->_joinAttributes[$attributeCode]) || $this->getEntity() instanceof \Magento\Eav\Model\Entity\AbstractEntity) {
            $this->_addAttributeJoin($attributeCode, $joinType);
        } else {
            $this->_addAttributeJoinOverride($attributeCode, $joinType);
        }
        return $this;
    }

    /**
     * Implementing public method to get attribute table alias, this has changed for Magento 1.6
     * @param string $attributeCode
     * @return string
     */
    public function getAttributeTableAlias($attributeCode)
    {
        return $this->_getAttributeTableAlias($attributeCode);
    }

    protected function _addAttributeJoinOverride($attributeCode, $joinType)
    {
        $attrTable = $this->_getAttributeTableAlias($attributeCode);
        $entity = $this->getEntity();
        $fKey = 'e.' . $entity->getEntityIdField();
        $pKey = $attrTable . '.' . $entity->getEntityIdField();
        $attribute = $entity->getAttribute($attributeCode);

        if (!$attribute) {
            throw new LocalizedException(__('Invalid attribute name: %1', $attributeCode));
        }

        if ($attribute->getBackend()->isStatic()) {
            $attrFieldName = $attrTable . '.' . $attribute->getAttributeCode();
        } else {
            $attrFieldName = $attrTable . '.value';
        }
        $connection = $this->getConnection();

        $fKey = $connection->quoteColumnAs($fKey, null);
        $pKey = $connection->quoteColumnAs($pKey, null);

        $condArr = ["{$pKey} = {$fKey}"];
        if (!$attribute->getBackend()->isStatic()) {
            $condArr[] = $this->getConnection()->quoteInto(
                $connection->quoteColumnAs("{$attrTable}.attribute_id", null) . ' = ?',
                $attribute->getId()
            );
        }

        /**
         * process join type
         */
        $joinMethod = $joinType === 'left' ? 'joinLeft' : 'join';

        $this->_joinAttributeToSelect($joinMethod, $attribute, $attrTable, $condArr, $attributeCode, $attrFieldName);

        $this->removeAttributeToSelect($attributeCode);
        $this->_filterAttributes[$attributeCode] = $attribute->getId();

        /**
         * Fix double join for using same as filter
         */
        $this->_joinFields[$attributeCode] = ['table' => '', 'field' => $attrFieldName];

        return $this;
    }
}
