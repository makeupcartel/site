<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml\Post;

use Aheadworks\Blog\Model\WordpressImport as WordpressImportModel;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Blog\Model\FileUploader;
use Aheadworks\Blog\Block\Adminhtml\System\Config\Field\WordpressImport as WordpressImportBlock;

/**
 * Class WordpressImport
 * @package Aheadworks\Blog\Controller\Adminhtml\Post
 */
class WordpressImport extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aheadworks_Blog::posts';

    /**
     * @var WordpressImportModel
     */
    private $wpImporter;

    /**
     * @var FileUploader
     */
    private $fileUploader;

    /**
     * @param WordpressImportModel $wpImporter
     * @param \Magento\Backend\App\Action\Context $context
     * @param FileUploader $fileUploader
     */
    public function __construct(
        WordpressImportModel $wpImporter,
        \Magento\Backend\App\Action\Context $context,
        FileUploader $fileUploader
    ) {
        $this->wpImporter = $wpImporter;
        $this->fileUploader = $fileUploader;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $result = $this->fileUploader->saveToTmpFolder(WordpressImportBlock::IMPORT_FILE_INPUT_ID);
            if ($this->isFileSavingFailed($result)) {
                throw new LocalizedException(__('Please provide valid xml file for import'));
            }
            $filePath = $this->fileUploader->getFullPath($result);
            $canOverride = (bool)$this->getRequest()->getParam('can_override_posts', false);
            $importedCount = $this->wpImporter->import($filePath, $canOverride);
            $this->messageManager->addSuccessMessage(__('%1 post(s) imported successfully', $importedCount));
        } catch (LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage(
                $exception,
                __('Something went wrong while importing posts')
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * Check if any error occurred during file saving
     *
     * @param array $result
     * @return bool
     */
    private function isFileSavingFailed($result)
    {
        return (isset($result['error']) && isset($result['errorcode']));
    }
}
