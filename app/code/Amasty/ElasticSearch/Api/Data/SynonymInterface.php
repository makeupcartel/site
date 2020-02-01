<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Api\Data;

interface SynonymInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const SYNONYM_ID = 'synonym_id';
    const TABLE_NAME = 'amasty_elastic_synonym';
    const STORE_ID = 'store_id';
    const TERM = 'term';
    /**#@-*/

    /**
     * @return int
     */
    public function getSynonymId();

    /**
     * @param int $synonymId
     *
     * @return \Amasty\ElasticSearch\Api\Data\SynonymInterface
     */
    public function setSynonymId($synonymId);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return \Amasty\ElasticSearch\Api\Data\SynonymInterface
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getTerm();

    /**
     * @param string $term
     *
     * @return \Amasty\ElasticSearch\Api\Data\SynonymInterface
     */
    public function setTerm($term);
}
