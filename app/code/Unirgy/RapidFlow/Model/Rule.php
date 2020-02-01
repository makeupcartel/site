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
namespace Unirgy\RapidFlow\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product as RfProduct;
use Unirgy\RapidFlow\Helper\Data as RfData;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product\CollectionFactory;
use Unirgy\RapidFlow\Model\Rule\Condition\Combine;

/**
 * Class Rule
 *
 * @method Combine getConditions()
 * @package Unirgy\RapidFlow\Model
 */
class Rule extends AbstractModel
{
    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var Combine
     */
    protected $_condition;

    /**
     * @var RfData
     */
    private $rfHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        Product $product,
        CollectionFactory $productCollectionFactory,
        Combine $condition,
        RfData $dataHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_product = $product;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_condition = $condition;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
        $this->rfHelper = $dataHelper;
    }

    protected $_productIds = null;

    /**
     * @param Profile $profile
     * @param array $rulePost
     * @return $this
     */
    public function parseConditionsPost($profile, array $rulePost)
    {
        $arr = $this->_convertFlatToRecursive($rulePost);
        if (isset($arr['conditions'])) {
            $profile->setConditions(
                $this->getConditions()
                    ->setConditions([])
                    ->loadArray($arr['conditions'][1])
                    ->asArray()
            );
        }
        return $this;
    }

    /**
     * @param Profile $profile
     * @return int[]
     */
    public function getProductIds($profile)
    {
        $collection = $this->_productCollectionFactory->create()
            ->setStore($profile->getStoreId());

        // collect conditions and join validated attributes
        $where = $this->getConditions()->asSqlWhere($collection);
        $_wf = $profile->getData('options/export/websites_filter');
        $_scs = $profile->getData('options/export/skip_configurable_simples');
        $entityId = 'entity_id';
        /*
        if ($this->rfHelper->hasMageFeature(RfProduct::ROW_ID)) {
            $entityId = RfProduct::ROW_ID;
        }
        */
        if (!$where && !$_wf && !$_scs) {
            return true;
        }
        if ($where) $collection->getSelect()->where($where);
        if ($_wf && !$collection->hasFlag('websites_filtered')) {
            $collection->getSelect()->join(
                array('__pw' => $collection->getTable('catalog_product_website')),
                'e.' . $entityId . '=__pw.product_id',
                []
            );
            $collection->getSelect()->where('__pw.website_id in (?)', $_wf);
            $collection->setFlag('websites_filtered', true);
        }
        if ($_scs && !$collection->hasFlag('skip_configurable_simples')) {
            $collection->getSelect()->joinLeft(
                array('__psl' => $collection->getTable('catalog_product_super_link')),
                'e.' . $entityId . '=__psl.product_id',
                []
            );
            $collection->getSelect()->where('__psl.product_id is NULL');
            $collection->setFlag('skip_configurable_simples', true);
        }
        return $collection->getAllIds();
    }

    public function getConditionsInstance()
    {
        return $this->_condition;
    }

    /**
     * Getter for rule actions collection instance
     *
     * @return \Magento\Rule\Model\Action\Collection
     */
    public function getActionsInstance()
    {
        return null;
    }
}
