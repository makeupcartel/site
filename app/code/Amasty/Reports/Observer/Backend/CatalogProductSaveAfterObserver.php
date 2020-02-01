<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Observer\Backend;

use Amasty\Reports\Api\RuleRepositoryInterface;
use Amasty\Reports\Model\Indexer\Rule\ProductProcessor;
use Amasty\Reports\Model\OptionSource\Rule\Status;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CatalogProductSaveAfterObserver
 * @package Amasty\Reports\Observer\Backend
 */
class CatalogProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    public function __construct(
        ProductProcessor $productProcessor,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->productProcessor = $productProcessor;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        if ($product) {
            if ($this->productProcessor->isIndexerScheduled()) {
                $this->ruleRepository->updateStatus(Status::PROCESSING);
            } else {
                $this->productProcessor->reindexRow($product->getId());
            }
        }
    }
}
