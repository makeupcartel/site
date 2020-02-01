<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost\DataProcessor;

/**
 * Interface DataProcessorInterface
 *
 * @package Aheadworks\Blog\Model\Indexer\MultiThread
 */
interface DataProcessorInterface
{
    /**
     * Inset data to table
     *
     * @param array $data
     * @param string $tableName
     */
    public function insertDataToTable($data, $tableName);
}
