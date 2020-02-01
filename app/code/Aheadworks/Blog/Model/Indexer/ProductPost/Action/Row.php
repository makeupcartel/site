<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Indexer\ProductPost\Action;

/**
 * Class Row
 * @package Aheadworks\Blog\Model\Indexer\ProductPost\Action
 */
class Row extends \Aheadworks\Blog\Model\Indexer\ProductPost\AbstractAction
{
    /**
     * Execute Row reindex
     *
     * @param int|null $id
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($id = null)
    {
        if (!isset($id) || empty($id)) {
            throw new \Magento\Framework\Exception\InputException(
                __('We can\'t rebuild the index for an undefined entity.')
            );
        }
        try {
            $this->resourceProductPostIndexer->reindexRows([$id]);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
