<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Rule;

use Amasty\Reports\Api\RuleRepositoryInterface;
use Magento\Backend\App\Action;
use Amasty\Reports\Controller\Adminhtml\Rule as RuleController;
use Psr\Log\LoggerInterface;

/**
 * Class Delete
 * @package Amasty\Reports\Controller\Adminhtml\Rule
 */
class Delete extends RuleController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    public function __construct(
        Action\Context $context,
        RuleRepositoryInterface $ruleRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->ruleRepository = $ruleRepository;
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
                $this->ruleRepository->deleteById($ruleId);
                $this->messageManager->addSuccessMessage(__('The rule has been deleted.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
                $resultRedirect->setPath('amasty_reports/*/edit', ['id' => $ruleId]);
            }
        }

        return $resultRedirect;
    }
}
