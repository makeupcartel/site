<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Model\Indexer\RelevanceRule;

use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\DataObject\IdentityInterface;

abstract class AbstractIndexer implements IndexerActionInterface, MviewActionInterface, IdentityInterface
{
    /**
     * @var IndexBuilder
     */
    private $indexBuilder;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cacheManager;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    private $cacheContext;

    public function __construct(
        IndexBuilder $indexBuilder,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\Indexer\CacheContext $cacheContext

    ) {
        $this->indexBuilder = $indexBuilder;
        $this->eventManager = $eventManager;
        $this->cacheManager = $cacheManager;
        $this->cacheContext = $cacheContext;
    }

    /**
     * @param int[] $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeFull()
    {
        $this->indexBuilder->reindexFull();
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);

        //was needed before MAGETWO-50668 was fixed
        $this->cacheManager->clean($this->getIdentities());
    }

    /**
     * @param int $productId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeRow($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t rebuild the index for an undefined entity.')
            );
        }

        $this->doExecuteRow($id);
    }

    /**
     * @param int[] $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function executeList(array $ids)
    {
        if (!$ids) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not rebuild index for empty products array')
            );
        }
        $this->doExecuteList($ids);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [
            \Magento\Catalog\Model\Category::CACHE_TAG,
            \Magento\Catalog\Model\Product::CACHE_TAG,
            \Magento\Framework\App\Cache\Type\Block::CACHE_TAG
        ];
    }

    /**
     * @param int $id
     */
    abstract protected function doExecuteRow($id);

    /**
     * @param int[] $ids
     */
    abstract protected function doExecuteList($ids);

    /**
     * @return IndexBuilder
     */
    protected function getIndexBuilder()
    {
        return $this->indexBuilder;
    }

    /**
     * @return \Magento\Framework\Indexer\CacheContext
     */
    protected function getCacheContext()
    {
        return $this->cacheContext;
    }
}
