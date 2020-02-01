<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\ResourceModel\Synonym;

use Amasty\ElasticSearch\Api\Data\SynonymInterface;
use Magento\Store\Model\Store;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'synonym_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\ElasticSearch\Model\Synonym::class,
            \Amasty\ElasticSearch\Model\ResourceModel\Synonym::class
        );
    }

    /**
     * @param array $ids
     */
    public function deleteByIds(array $ids)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['synonym_id IN(?)' => implode(',', $ids)]
        );
    }

    /**
     * @param string $searchString
     * @return $this
     */
    public function addSearchStringFilter($searchString)
    {
        $this->getSelect()
            ->where(
                SynonymInterface::TERM . ' ?',
                new \Zend_Db_Expr('REGEXP \'' . addslashes($searchString) . '\'')
            );

        return $this;
    }

    /**
     * Add filter by store
     *
     * @param null $store
     * @return $this
     */
    public function addStoreFilter($store = null)
    {
        $storeId = $store;
        if ($store instanceof Store) {
            $storeId = $store->getId();
        }
        $this->getSelect()->where('store_id = ?', $storeId);

        return $this;
    }
}
