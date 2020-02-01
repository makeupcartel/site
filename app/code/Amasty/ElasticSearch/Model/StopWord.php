<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model;

use Amasty\ElasticSearch\Api\Data\StopWordInterface;

class StopWord extends \Magento\Framework\Model\AbstractModel implements StopWordInterface
{
    /**
     * Model Init
     *
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\ElasticSearch\Model\ResourceModel\StopWord::class);
        $this->setIdFieldName('stop_word_id');
    }

    /**
     * @inheritdoc
     */
    public function getStopWordId()
    {
        return $this->_getData(StopWordInterface::STOP_WORD_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStopWordId($stopWordId)
    {
        $this->setData(StopWordInterface::STOP_WORD_ID, $stopWordId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTerm()
    {
        return $this->_getData(StopWordInterface::TERM);
    }

    /**
     * @inheritdoc
     */
    public function setTerm($term)
    {
        $this->setData(StopWordInterface::TERM, $term);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->_getData(StopWordInterface::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->setData(StopWordInterface::STORE_ID, $storeId);

        return $this;
    }
}
