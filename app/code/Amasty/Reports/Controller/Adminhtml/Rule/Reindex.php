<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Rule;

use Amasty\Reports\Api\RuleRepositoryInterface;
use Amasty\Reports\Model\Indexer\Rule\RuleProcessor;
use Amasty\Reports\Model\OptionSource\Rule\Status;
use Exception;
use Magento\Backend\App\Action;
use Amasty\Reports\Controller\Adminhtml\Rule as RuleController;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class Reindex
 * @package Amasty\Reports\Controller\Adminhtml\Rule
 */
class Reindex extends RuleController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var RuleProcessor
     */
    private $ruleProcessor;

    public function __construct(
        Action\Context $context,
        RuleRepositoryInterface $ruleRepository,
        RuleProcessor $ruleProcessor,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->ruleRepository = $ruleRepository;
        $this->ruleProcessor = $ruleProcessor;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create()
            ->setPath('amasty_reports/*');
        $ruleId = (int)$this->getRequest()->getParam('id');
        if ($ruleId) {
            try {
                $this->ruleProcessor->reindexRow($ruleId, true);
                $this->ruleRepository->updateStatus(Status::INDEXED, $ruleId);
                $resultRedirect->setPath('amasty_reports/*/edit', ['id' => $ruleId]);
                $this->messageManager->addSuccessMessage(__('The rule has been reindexed.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t reindex item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $resultRedirect->setPath('amasty_reports/*/edit', ['id' => $ruleId]);
            }
        }

        return $resultRedirect;
    }
}
