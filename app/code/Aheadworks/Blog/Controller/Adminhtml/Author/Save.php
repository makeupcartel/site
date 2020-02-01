<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Author;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Ui\DataProvider\AuthorDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Blog\Api\AuthorRepositoryInterface;
use Aheadworks\Blog\Api\Data\AuthorInterfaceFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Backend\App\Action;

/**
 * Class Save
 * @package Aheadworks\Blog\Controller\Adminhtml\Author
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Blog::authorss';

    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @var AuthorInterfaceFactory
     */
    private $authorDataFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @param Context $context
     * @param AuthorRepositoryInterface $authorRepository
     * @param AuthorInterfaceFactory $authorDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param StoreManagerInterface $storeManager
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        AuthorRepositoryInterface $authorRepository,
        AuthorInterfaceFactory $authorDataFactory,
        DataObjectHelper $dataObjectHelper,
        StoreManagerInterface $storeManager,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->authorRepository = $authorRepository;
        $this->authorDataFactory = $authorDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->storeManager = $storeManager;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($authorData = $this->getRequest()->getPostValue()) {
            $authorData = $this->prepareData($authorData);
            $authorId = isset($authorData[AuthorInterface::ID]) ? $authorData[AuthorInterface::ID] : false;
            try {
                $authorDataObject = $authorId
                    ? $this->authorRepository->get($authorId)
                    : $this->authorDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $authorDataObject,
                    $authorData,
                    AuthorInterface::class
                );
                $this->authorRepository->save($authorDataObject);
                $this->dataPersistor->clear(AuthorDataProvider::DATA_PERSISTOR_KEY);
                $this->messageManager->addSuccessMessage(__('The author was successfully saved.'));
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('Something went wrong while saving the author.')
                );
            }
            $this->dataPersistor->set(AuthorDataProvider::DATA_PERSISTOR_KEY, $authorData);
            if ($authorId) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $authorId, '_current' => true]);
            }
            return $resultRedirect->setPath('*/*/new', ['_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Prepare data
     *
     * @param array $data
     * @return array
     */
    private function prepareData($data)
    {
        if (isset($data[AuthorInterface::IMAGE_FILE][0]['file'])) {
            $data[AuthorInterface::IMAGE_FILE] = $data[AuthorInterface::IMAGE_FILE][0]['file'];
        } else {
            $data[AuthorInterface::IMAGE_FILE] = '';
        }
        
        return $data;
    }
}
