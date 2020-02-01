<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\ResourceModel\StopWord;

use Magento\Store\Model\Store;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'stop_word_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Amasty\ElasticSearch\Model\StopWord::class,
            \Amasty\ElasticSearch\Model\ResourceModel\StopWord::class
        );
    }

    public function deleteByIds($ids)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['stop_word_id IN(?)' => implode(',', $ids)]
        );
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
