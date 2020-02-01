<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Controller\Adminhtml\Request;

class MassDelete extends \Amasty\Groupcat\Controller\Adminhtml\Request
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\Groupcat\Model\ResourceModel\Request\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Amasty\Groupcat\Model\RequestRepository $requestRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Groupcat\Model\ResourceModel\Request\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $requestRepository, $coreRegistry);
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $requestIds = $this->getRequest()->getParam('request_ids');
        if (!is_array($requestIds)) {
            $this->messageManager->addErrorMessage(__('Please select items.'));
        } else {
            try {
                /** @var \Amasty\Groupcat\Model\ResourceModel\Request\Collection $collection */
                $collection = $this->collectionFactory->create();
                $collection->deleteByIds($requestIds);

                $this->messageManager->addSuccessMessage(__('Get a Quote requests are deleted.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete items right now. Please review the log and try again.')
                );
                $this->logger->critical($e);
            }
        }
        $this->_redirect('amasty_groupcat/*/');
    }
}
