<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Indexer\ProductPost\Action;

/**
 * Class Rows
 * @package Aheadworks\Blog\Model\Indexer\ProductPost\Action
 */
class Rows extends \Aheadworks\Blog\Model\Indexer\ProductPost\AbstractAction
{
    /**
     * Execute Rows reindex
     *
     * @param array $ids
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids)
    {
        if (empty($ids)) {
            throw new \Magento\Framework\Exception\InputException(__('Bad value was supplied.'));
        }
        try {
            $this->resourceProductPostIndexer->reindexRows($ids);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
