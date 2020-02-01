<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Rule;

use Amasty\Reports\Api\Data\RuleInterface;
use Amasty\Reports\Model\Rule;
use Amasty\Reports\Api\RuleRepositoryInterface;
use Amasty\Reports\Controller\Adminhtml\Rule as RuleController;
use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 * @package Amasty\Reports\Controller\Adminhtml\Rule
 */
class Save extends RuleController
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $dataObject;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    public function __construct(
        Action\Context $context,
        RuleRepositoryInterface $ruleRepository,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\DataObject $dataObject,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->dataObject = $dataObject;
        $this->logger = $logger;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create()
            ->setPath('*/*/');
        $data = $this->getRequest()->getPostValue();
        $ruleId = (int)$this->getRequest()->getParam(RuleInterface::ENTITY_ID);
        if ($data) {
            /** @var Rule $model */
            if ($ruleId) {
                $model = $this->ruleRepository->getById($ruleId);
            } else {
                $model = $this->ruleRepository->getNewRule();
            }

            try {
                $data = $this->prepareData($data, $model);
                $model->addData($data);
                $model->loadPost($data);
                $model = $this->ruleRepository->save($model);

                $this->messageManager->addSuccessMessage(__('The Rule was successfully saved.'));
                $this->dataPersistor->clear(RuleInterface::PERSIST_NAME);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('amasty_reports/*/edit', ['id' => $model->getEntityId()]);
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if (empty($ruleId)) {
                    $resultRedirect->setPath('amasty_reports/*/newAction');
                } else {
                    $resultRedirect->setPath('amasty_reports/*/edit', ['id' => $ruleId]);
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->dataPersistor->set(RuleInterface::PERSIST_NAME, $data);
                $resultRedirect->setPath('amasty_reports/*/edit', ['id' => $ruleId]);
            }
        }

        return $resultRedirect;
    }

    /**
     * @param array $data
     * @param Rule $rule
     *
     * @return array
     */
    private function prepareData($data, $rule)
    {
        if (isset($data[RuleInterface::ENTITY_ID]) && empty($data[RuleInterface::ENTITY_ID])) {
            $data[RuleInterface::ENTITY_ID] = null;
        }

        if (isset($data['rule']) && isset($data['rule']['conditions'])) {
            $conditions = $data['rule']['conditions'];
            unset($data['rule']);
            $data['conditions'] = $conditions;
        }

        return $data;
    }
}
