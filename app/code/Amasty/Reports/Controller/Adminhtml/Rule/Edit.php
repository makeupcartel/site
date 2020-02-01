<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Rule;

use Amasty\Reports\Controller\Adminhtml\Rule as RuleController;
use Amasty\Reports\Api\RuleRepositoryInterface;
use Amasty\Reports\Api\Data\RuleInterface;
use Amasty\Reports\Model\Rule;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Amasty\Reports\Controller\Adminhtml\Rule
 */
class Edit extends RuleController
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        DataPersistorInterface $dataPersistor,
        Registry $coreRegistry,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->ruleRepository = $ruleRepository;
        $this->dataPersistor = $dataPersistor;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $ruleId = (int)$this->getRequest()->getParam('id');
        if ($ruleId) {
            try {
                $model = $this->ruleRepository->getById($ruleId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Rule no longer exists.'));
                $this->_redirect('*/*/index');

                return;
            }
        } else {
            /** @var Rule $model */
            $model = $this->ruleRepository->getNewRule();
        }

        // set entered data if was error when we do save
        $data = $this->dataPersistor->get(RuleInterface::PERSIST_NAME);
        if (!empty($data) && !$model->getEntityId()) {
            $model->addData($data);
        }

        $this->coreRegistry->register(RuleInterface::PERSIST_NAME, $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $breadcrumb = $model->getEntityId() ?
            __('Edit Rule # %1', $model->getEntityId())
            : __('New Rule');
        $resultPage->addBreadcrumb($breadcrumb, $breadcrumb);
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getEntityId() ?
                __('Edit Rule # %1', $model->getEntityId())
                : __('New Rule')
        );

        return $resultPage;
    }
}
