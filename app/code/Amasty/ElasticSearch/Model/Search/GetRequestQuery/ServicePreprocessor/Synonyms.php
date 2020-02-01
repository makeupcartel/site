<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Search\GetRequestQuery\ServicePreprocessor;

use Amasty\ElasticSearch\Api\SynonymRepositoryInterface;

class Synonyms implements PreprocessorInterface
{
    const SYNONYM_DELEMITER = '$';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SynonymRepositoryInterface
     */
    private $synonymRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SynonymRepositoryInterface $synonymRepository
    ) {
        $this->storeManager = $storeManager;
        $this->synonymRepository = $synonymRepository;
    }

    /**
     * @param string $query
     * @return string
     */
    public function process($query)
    {
        if (!$query) {
            return $query;
        }

        $storeId = $this->storeManager->getStore()->getId();

        $synonymsArray = $this->synonymRepository->getSynonymsByQuery($query, $storeId);
        if (!empty($synonymsArray)) {
            $synonyms = [];
            foreach ($synonymsArray as $synonymPart) {
                $synonyms [] = implode(self::SYNONYM_DELEMITER, $synonymPart);
            }
            $query = implode(' ', $synonyms);
        }

        return $query;
    }
}
