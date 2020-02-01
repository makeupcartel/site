<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Model\ResourceModel\UserSearch;

use Magento\Framework\DB\Select;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    const GROUP_BY_MOUNTH = 'm';

    const GROUP_BY_DAY = 'd';

    const LIMIT_LAST_DATA = 10;

    protected function _construct()
    {
        $this->_init(\Amasty\Xsearch\Model\UserSearch::class, \Amasty\Xsearch\Model\ResourceModel\UserSearch::class);
    }

    /**
     * @param string $groupBy
     * @return $this
     */
    public function getPopularity($groupBy = '')
    {
        $this->getSelect()->reset(Select::COLUMNS)->columns(['COUNT(query_id) as popularity']);
        $this->groupByDate($groupBy);

        return $this;
    }

    /**
     * @param string $groupBy
     * @return $this
     */
    public function getUniqueSearch($groupBy = '')
    {
        $this->getSelect()->reset(Select::COLUMNS)->columns('COUNT(DISTINCT query_id) as unique_query');
        $this->groupByDate($groupBy);

        return $this;
    }

    /**
     * @param string $groupBy
     * @return $this
     */
    public function getUniqueUsers($groupBy = '')
    {
        $this->getSelect()->reset(Select::COLUMNS)->columns('COUNT(DISTINCT user_key) as unique_user');
        $this->groupByDate($groupBy);

        return $this;
    }

    /**
     * @param string $groupBy
     * @return $this
     */
    public function getProductClickUsers($groupBy = '')
    {
        $this->getSelect()->reset(Select::COLUMNS)
            ->columns(['COUNT(DISTINCT user_key) as product_click'])
            ->where('product_click IS NOT NULL');
        $this->groupByDate($groupBy);

        return $this;
    }

    /**
     * @param $groupBy
     */
    private function groupByDate($groupBy)
    {
        if ($groupBy) {
            $this->getSelect()
                ->group('DATE_FORMAT(created_at, "%Y%' . $groupBy . '")')
                ->columns('created_at')
                ->order('created_at DESC');
        }
    }

    /**
     * @return int
     */
    public function getTotalRowPopularity()
    {
        return (int)$this->getPopularity()->setLimit(1)->getFirstItem()->getPopularity();
    }

    /**
     * @return int
     */
    public function getTotalRowUniqueQuery()
    {
        return (int)$this->getUniqueSearch()->setLimit(1)->getFirstItem()->getUniqueQuery();
    }

    /**
     * @return int
     */
    public function getTotalRowUniqueUsers()
    {
        return (int)$this->getUniqueUsers()->setLimit(1)->getFirstItem()->getUniqueUser();
    }

    /**
     * @return int
     */
    public function getTotalRowProductClick()
    {
        return $this->getProductClickUsers()->setLimit(1)->getFirstItem()->getProductClick();
    }

    /**
     * @return $this
     */
    public function getSearchQueries($limit)
    {
        $this->getSelect()->joinLeft(
            ['query' => $this->getTable('search_query')],
            'query.query_id = main_table.query_id'
        )
            ->reset(Select::COLUMNS)
            ->columns([
                'query.query_text',
                'COUNT(query.query_text) as total_searches',
                'COUNT(DISTINCT user_key) as user_key',
                'query.query_id'
            ])
            ->where('query_text IS NOT NULL')
            ->group('query.query_text')
            ->order('total_searches DESC')
            ->limit($limit);

        return $this;
    }

    /**
     * @return $this
     */
    public function getProductClickForQuery($queryId)
    {
        $this->getSelect()
            ->reset(Select::COLUMNS)
            ->columns(['COUNT(DISTINCT user_key) as product_click'])
            ->where('query_id = ?', $queryId)
            ->where('product_click IS NOT NULL')
            ->limit(1);

        return (int)$this->getFirstItem()->getProductClick();
    }

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->getSelect()->limit($limit);

        return $this;
    }
}
