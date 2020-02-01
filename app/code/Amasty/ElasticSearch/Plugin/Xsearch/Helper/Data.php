<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\Xsearch\Helper;

use Amasty\ElasticSearch\Model\ResourceModel\StopWord\CollectionFactory;

class Data
{
    /**
     * @var CollectionFactory
     */
    private $stopWordCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var null
     */
    private $stopWords = null;

    public function __construct(
        CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->stopWordCollectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Amasty\Xsearch\Helper\Data $subject
     * @param string $text
     * @param string $query
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeHighlight(
        \Amasty\Xsearch\Helper\Data $subject,
        $text,
        $query
    ) {
        if ($query) {
            $words = explode(' ', $query);
            foreach ($words as $key => $word) {
                if (trim($word) !== '' && in_array($word, $this->getStopWords(), true)) {
                    unset($words[$key]);
                }
            }
            $query = implode(' ', $words);
        }

        return [$text, $query];
    }

    private function getStopWords()
    {
        if ($this->stopWords === null) {
            $this->stopWords = [];
            $collection = $this->stopWordCollectionFactory->create()
                ->addStoreFilter($this->storeManager->getStore()->getId());
            foreach ($collection as $stopWord) {
                $this->stopWords[$stopWord->getId()] = $stopWord->getTerm();
            }
        }
        return $this->stopWords;
    }
}
