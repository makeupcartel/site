<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery\ServicePreprocessor;

use Amasty\ElasticSearch\Api\StopWordRepositoryInterface;

class Stopwords implements PreprocessorInterface
{
    /**
     * @var StopWordRepositoryInterface
     */
    private $stopWordRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StopWordRepositoryInterface $stopWordRepository
    ) {
        $this->stopWordRepository = $stopWordRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $query
     * @return string
     */
    public function process($query)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $stopWords = $this->stopWordRepository->getArrayListByStoreId($storeId);
        $origQuery = $query;

        // remove stop words from query
        $query = explode(' ', $query);
        $query = implode(' ', array_diff($query, $stopWords));
        $query = trim($query);

        if (!$query) {
            $query = $origQuery;//return query if query is only from stop words
        }

        return $query;
    }
}
