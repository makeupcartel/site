<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model;

use Amasty\ElasticSearch\Api\Data\SynonymInterface;

class Synonym extends \Magento\Framework\Model\AbstractModel implements SynonymInterface
{
    /**
     * Model Init
     *
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\ElasticSearch\Model\ResourceModel\Synonym');
        $this->setIdFieldName('synonym_id');
    }

    /**
     * @inheritdoc
     */
    public function getSynonymId()
    {
        return $this->_getData(SynonymInterface::SYNONYM_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSynonymId($synonymId)
    {
        $this->setData(SynonymInterface::SYNONYM_ID, $synonymId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->_getData(SynonymInterface::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->setData(SynonymInterface::STORE_ID, $storeId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTerm()
    {
        return $this->_getData(SynonymInterface::TERM);
    }

    /**
     * @inheritdoc
     */
    public function setTerm($term)
    {
        $this->setData(SynonymInterface::TERM, $term);

        return $this;
    }
}
