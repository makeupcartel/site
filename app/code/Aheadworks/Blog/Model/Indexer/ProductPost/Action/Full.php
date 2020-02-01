<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Indexer\ProductPost\Action;

use Aheadworks\Blog\Model\ResourceModel\Indexer\ProductPost as ResourceProductPostIndexer;
use Magento\Framework\App\ProductMetadataInterface;
use Aheadworks\Blog\Model\Indexer\ProductPost\AbstractAction;
use Aheadworks\Blog\Model\Indexer\ProductPost\Action\Full\MultiThreadProcessor;
use Aheadworks\Blog\Model\Indexer\ProductPost\Action\Full\SingleThreadProcessor;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Full
 *
 * @package Aheadworks\Blog\Model\Indexer\ProductPost\Action
 */
class Full extends AbstractAction
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var MultiThreadProcessor
     */
    private $multiThreadProcessor;

    /**
     * @var SingleThreadProcessor
     */
    private $singleThreadProcessor;

    /**
     * @param ResourceProductPostIndexer $resourceProductPostIndexer
     * @param ProductMetadataInterface $productMetadata
     * @param SingleThreadProcessor $singleThreadProcessor
     * @param MultiThreadProcessor $multiThreadProcessor
     */
    public function __construct(
        ResourceProductPostIndexer $resourceProductPostIndexer,
        ProductMetadataInterface $productMetadata,
        SingleThreadProcessor $singleThreadProcessor,
        MultiThreadProcessor $multiThreadProcessor
    ) {
        parent::__construct($resourceProductPostIndexer);
        $this->productMetadata = $productMetadata;
        $this->singleThreadProcessor = $singleThreadProcessor;
        $this->multiThreadProcessor = $multiThreadProcessor;
    }

    /**
     * Execute Full reindex depending on processor
     *
     * @param array|int|null $ids
     * @return void
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute($ids = null)
    {
        $magentoVersion = $this->productMetadata->getVersion();
        if (version_compare($magentoVersion, '2.2.6', '>=')) {
            $this->multiThreadProcessor->execute($ids);
        } else {
            $this->singleThreadProcessor->execute($ids);
        }
    }
}
