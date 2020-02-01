<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Author\Grid;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Aheadworks\Blog\Model\ResourceModel\Author\Collection as AuthorCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Class Collection
 * @package Aheadworks\Blog\Model\ResourceModel\Author\Grid
 */
class Collection extends AuthorCollection implements SearchResultInterface
{
    /**
     * Full name
     */
    const FULLNAME = 'fullname';

    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param $mainTable
     * @param $resourceModel
     * @param string $model
     * @param null $connection
     * @param AbstractDb|null $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $resourceModel,
        $model = Document::class,
        $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFullNameToSelect();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Add full name to select
     *
     * @return $this
     */
    protected function addFullNameToSelect()
    {
        $fullNameExpr = $this->getFullNameExpr();
        $select = $this->getSelect();

        $select->columns([self::FULLNAME => $fullNameExpr]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_string($field) && $field == self::FULLNAME) {
            $this->addFullNameFilter($condition);
        } else {
            return parent::addFieldToFilter($field, $condition);
        }

        return $this;
    }

    /**
     * Add full name filter
     *
     * @param null|string|array $condition
     * @return $this
     */
    private function addFullNameFilter($condition)
    {
        $connection = $this->getResource()->getConnection();
        $select = $this->getSelect();
        $fullNameExpr = $this->getFullNameExpr();
        $sqlCondition = $connection->prepareSqlCondition($fullNameExpr, $condition);
        $select->where($sqlCondition);

        return $this;
    }

    /**
     * Get full name expr
     *
     * @return \Zend_Db_Expr
     */
    private function getFullNameExpr()
    {
        return $this->getConnection()->getConcatSql(
            [
                AuthorInterface::FIRSTNAME,
                AuthorInterface::LASTNAME
            ],
            ' '
        );
    }
}
