<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\Indexer\Rule;

use Amasty\Reports\Model\Rule;
use Amasty\Reports\Api\Data\RuleInterface;
use Amasty\Reports\Model\ResourceModel\RuleIndex;
use Amasty\Reports\Model\ResourceModel\RuleIndexFactory;
use Amasty\Reports\Api\RuleRepositoryInterface;
use Amasty\Reports\Model\OptionSource\Rule\Status;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class AbstractIndexer
 * @package Amasty\Reports\Model\Indexer\Rule
 */
abstract class AbstractIndexer implements ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var RuleIndex
     */
    private $resourceIndex;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var int
     */
    protected $batchCount;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    public function __construct(
        RuleIndexFactory $resourceIndexFactory,
        RuleRepositoryInterface $ruleRepository,
        ProductCollectionFactory $productCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ManagerInterface $eventManager,
        DateTimeFactory $dateFactory,
        $batchCount = 1000
    ) {
        $this->resourceIndex = $resourceIndexFactory->create();
        $this->ruleRepository = $ruleRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->batchCount = $batchCount;
        $this->eventManager = $eventManager;
        $this->dateFactory = $dateFactory;
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->doReindex();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     *
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->doReindex($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     *
     * @return void
     */
    public function executeRow($id)
    {
        $ids = [$id];
        $this->doReindex($ids);
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     *
     * @return void
     * @api
     */
    public function execute($ids)
    {
        $this->doReindex($ids);
    }

    /**
     * @param array $ids
     */
    protected function clean($ids = [])
    {
        if (empty($ids)) {
            $this->getIndexResource()->cleanAllIndex();
        } else {
            $this->cleanList($ids);
        }
    }

    /**
     * @return RuleIndex
     */
    protected function getIndexResource()
    {
        return $this->resourceIndex;
    }

    /**
     * @param $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    private function loadRules($searchCriteria)
    {
        return $this->ruleRepository->getList($searchCriteria);
    }

    /**
     * @param array $ids
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    protected function getRules($ids = [])
    {
        if (!empty($ids)) {
            $this->searchCriteriaBuilder->addFilter(RuleInterface::ENTITY_ID, $ids, 'in');
        }

        return $this->loadRules($this->searchCriteriaBuilder->create());
    }

    /**
     * @param int $status
     * @param int $ruleId
     */
    protected function updateStatus($status, $ruleId)
    {
        $this->ruleRepository->updateStatus($status, $ruleId);
    }

    /**
     * @param $ruleId
     */
    protected function updateLastUpdated($ruleId)
    {
        $this->ruleRepository->updateLastUpdated($this->getCurrentTime(), $ruleId);
    }

    /**
     * @return string
     */
    protected function getCurrentTime()
    {
        return $this->dateFactory->create()->gmtDate();
    }

    /**
     * @param array $ids
     *
     * @return void
     */
    abstract protected function cleanList($ids);

    /**
     * @param array $ids
     *
     * @return void
     */
    protected function doReindex($ids = [])
    {
        $rows = [];
        $count = 0;
        $this->clean($ids);

        /** @var Rule $rule */
        foreach ($this->getProcessedRules($ids) as $rule) {
            $ruleId = $rule->getEntityId();
            $this->setProductsFilter($rule, $ids);
            $matchedProducts = $rule->getMatchingProductIdsByReportRule();
            foreach ($matchedProducts as $productId => $storeIds) {
                while ($storeIds) {
                    $rows[] = [
                        RuleIndex::PRODUCT_ID => $productId,
                        RuleIndex::STORE_ID => array_shift($storeIds),
                        RuleIndex::RULE_ID => $ruleId
                    ];
                    if (++$count > $this->batchCount) {
                        $this->getIndexResource()->insertIndexData($rows);
                        $count = 0;
                        $rows = [];
                    }
                }
            }
            $this->updateStatus(Status::INDEXED, $ruleId);
            $this->updateLastUpdated($ruleId);
        }

        if (!empty($rows)) {
            $this->getIndexResource()->insertIndexData($rows);
        }
    }

    /**
     * @param array $ids
     *
     * @return \Magento\Framework\Api\ExtensibleDataInterface[]
     */
    abstract protected function getProcessedRules($ids = []);

    /**
     * @param Rule $rule
     * @param int|array $productIds
     */
    abstract protected function setProductsFilter($rule, $productIds);
}
