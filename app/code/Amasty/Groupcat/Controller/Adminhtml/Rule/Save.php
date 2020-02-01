<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Controller\Adminhtml\Rule;

class Save extends \Amasty\Groupcat\Controller\Adminhtml\Rule
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $dataObject;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context                   $context
     * @param \Magento\Framework\Registry                           $coreRegistry
     * @param \Amasty\Groupcat\Api\RuleRepositoryInterface          $ruleRepository
     * @param \Amasty\Groupcat\Model\RuleFactory                    $ruleFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\DataObject                         $dataObject
     * @param \Psr\Log\LoggerInterface                              $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Groupcat\Api\RuleRepositoryInterface $ruleRepository,
        \Amasty\Groupcat\Model\RuleFactory $ruleFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\DataObject $dataObject,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $ruleRepository,
            $ruleFactory
        );
        $this->dataPersistor = $dataPersistor;
        $this->dataObject = $dataObject;
        $this->logger = $logger;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {

            /** @var \Amasty\Groupcat\Model\Rule $model */
            $model = $this->ruleFactory->create();

            try {
                $data = $this->getRequest()->getPostValue();
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model = $this->ruleRepository->get($id);
                }

                $validateResult = $model->validateData($this->dataObject->addData($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->dataPersistor->set('amasty_groupcat_rule', $data);
                    $this->_redirect('amasty_groupcat/*/edit', ['id' => $model->getId()]);
                    return;
                }

                if (isset($data['rule'])) {
                    if (isset($data['rule']['conditions'])) {
                        $data['conditions'] = $data['rule']['conditions'];
                    }
                    if (isset($data['rule']['actions'])) {
                        $data['actions'] = $data['rule']['actions'];
                    }
                    unset($data['rule']);
                }
                if (!isset($data['category_ids'])) {
                    $data['category_ids'] = [];
                }

                $model->loadPost($data);

                $this->_getSession()->setPageData($data);
                $this->dataPersistor->set('amasty_groupcat_rule', $data);

                $this->ruleRepository->save($model);

                $this->messageManager->addSuccessMessage(__('The rule is saved.'));
                $this->_getSession()->setPageData(false);
                $this->dataPersistor->clear('amasty_groupcat_rule');

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_groupcat/*/edit', ['id' => $model->getId()]);
                    return;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('amasty_groupcat/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amasty_groupcat/*/new');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_getSession()->setPageData($data);
                $this->dataPersistor->set('amasty_groupcat_rule', $data);
                $this->_redirect('amasty_groupcat/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
                return;
            }
        }
        $this->_redirect('amasty_groupcat/*/');
    }
}
