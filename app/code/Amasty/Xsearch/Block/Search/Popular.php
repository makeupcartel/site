<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

class Popular extends AbstractSearch
{
    const CATEGORY_BLOCK_POPULAR = 'popular_searches';

    public function getBlockType()
    {
        return self::CATEGORY_BLOCK_POPULAR;
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
     * @return \Magento\Search\Model\ResourceModel\Query\Collection
     */
    protected function generateCollection()
    {
        return $this->queryFactory->get()->getSuggestCollection()
            ->setPageSize($this->getLimit());
    }
    /**
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    public function getName(\Magento\Framework\DataObject $item)
    {
        return $this->generateName($item->getQueryText());
    }
}
