<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

class Recent extends AbstractSearch
{
    const CATEGORY_BLOCK_RECENT = 'recent_searches';

    public function getBlockType()
    {
        return self::CATEGORY_BLOCK_RECENT;
    }

    /**
     * @inheritdoc
     */
    public function getResults()
    {
        $result = parent::getResults();
        foreach ($this->getSearchCollection() as $index => $item) {
            $result[$index]['num_results'] = $item->getNumResults();
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function generateCollection()
    {
        $collection = parent::generateCollection()
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setRecentQueryFilter()
            ->setPageSize($this->getLimit());
        $collection
            ->getSelect()
            ->where('num_results > 0 AND display_in_terms = 1');
        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getQueryText());
    }
}
