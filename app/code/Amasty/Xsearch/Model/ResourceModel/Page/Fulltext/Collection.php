<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\ResourceModel\Page\Fulltext;

use Zend_Db_Expr;

class Collection extends \Magento\Cms\Model\ResourceModel\Page\Collection
{
    const FULLTEXT_SPECIAL_CHAR_LIST = '+-*';

    /** @var string */
    private $queryText;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var array
     */
    protected $weights = [
       'title' => 3,
       'content' => 2
    ];

    /**
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        $this->queryText = trim($this->queryText . ' ' . $query);

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->setStoreId($this->storeManager->getStore()->getId());
        }

        return $this->storeId;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof \Magento\Store\Model\Store) {
            $storeId = $storeId->getId();
        }
        $this->storeId = (int)$storeId;

        return $this;
    }

    /**
     * @param  \Magento\Cms\Model\ResourceModel\Page\Collection $collection
     * @param $indexTable
     * @return array
     */
    protected function getFulltextIndexColumns($collection, $indexTable)
    {
        $indexes = $collection->getConnection()->getIndexList($indexTable);
        foreach ($indexes as $index) {
            if (strtoupper($index['INDEX_TYPE']) == 'FULLTEXT') {
                return $index['COLUMNS_LIST'];
            }
        }

        return [];
    }

    /**
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getSearchQuery()) {
            $this->getSelect()
                ->where($this->getMatchCondition(), $this->getSearchQuery())
                ->order(new Zend_Db_Expr(
                    $this->getConnection()->quoteInto($this->getMatchCondition(), $this->getSearchQuery())
                    . ' DESC'
                ));
        }

        parent::_renderFiltersBefore();
    }

    /**
     * @return string
     */
    private function getMatchCondition()
    {
        $query = trim($this->queryText, self::FULLTEXT_SPECIAL_CHAR_LIST);
        $columns = $this->getFulltextIndexColumns($this, $this->getMainTable());
        $matchMode = (strlen($query) > 2) ? ' IN BOOLEAN MODE' : '';
        return 'MATCH(' . implode(',', $columns) . ") AGAINST(?$matchMode)";
    }

    /**
     * @return string
     */
    private function getSearchQuery()
    {
        $query = trim($this->queryText, self::FULLTEXT_SPECIAL_CHAR_LIST);
        if (strlen($query) > 2) {
            $query .= '*';
        }

        return $query;
    }

    /**
     * @return array
     */
    public function getIndexFulltextValues()
    {
        $fulltextValues = [];
        foreach ($this->getItems() as $id => $item) {
            $fulltextString = '';
            $indexColumns = $this->getFulltextIndexColumns($this, $this->getMainTable());
            foreach ($indexColumns as $indexColumn) {
                if ($item->getData($indexColumn)) {
                    $fulltextString .= ' ' . trim($item->getData($indexColumn));
                }
            }

            $fulltextValues[$id] = trim($fulltextString);
        }

        return $fulltextValues;
    }
}
