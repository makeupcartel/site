<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\Stopword;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Amasty\ElasticSearch\Api\Data\StopWordInterface;

class DoImport extends Action
{
    const ADMIN_RESOURCE = 'Amasty_ElasticSearch::stop_words';
    const MEDIA_PATH = 'amasty/elastic/import';
    const FILE_WAS_NOT_UPLOADED_CODE_ERROR = '666';

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $fileUploaderFactory;

    /**
     * @var \Amasty\ElasticSearch\Model\StopWordRepository
     */
    private $stopWordRepository;

    /**
     * @var \Amasty\ElasticSearch\Model\StopWordFactory
     */
    private $stopWordFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Amasty\ElasticSearch\Model\StopWordRepository $stopWordRepository,
        \Amasty\ElasticSearch\Model\StopWordFactory $stopWordFactory
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->stopWordRepository = $stopWordRepository;
        $this->stopWordFactory = $stopWordFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $storeId = (int) $this->getRequest()->getParam(StopWordInterface::STORE_ID);
        if ($data) {
            try {
                //upload images
                $path = $this->filesystem->getDirectoryRead(
                    DirectoryList::MEDIA
                )->getAbsolutePath(
                    self::MEDIA_PATH
                );

                $this->ioFile->checkAndCreateFolder($path);

                try {
                    /** @var $uploader \Magento\MediaStorage\Model\File\Uploader */
                    $uploader = $this->fileUploaderFactory->create(['fileId' => 'amasty_elastic_file_upload']);
                    $uploader->setAllowedExtensions(['csv']);
                    $uploader->setAllowRenameFiles(true);
                    $result = $uploader->save($path);
                } catch (\Exception $ex) {
                    throw new LocalizedException(__($ex->getMessage()));
                }

                if (isset($result['file']) && $this->ioFile->fileExists($path . '/' . $result['file'])) {
                    $file = $path . '/' . $result['file'];
                } else {
                    throw new LocalizedException(__('Something wend wrong during saving file'));
                }

                $count = $this->stopWordRepository->importStopWords($file, $storeId);
                $this->ioFile->rm($file);
            } catch (LocalizedException $ex) {
                $this->messageManager->addErrorMessage($ex->getMessage());
                return $resultRedirect->setPath('*/*/import');
            } catch (\Exception $ex) {
                $this->messageManager->addErrorMessage(__('Something went wrong. Please try again'));
                return $resultRedirect->setPath('*/*/import');
            }

            if (isset($count)) {
                $this->messageManager->addSuccessMessage(__('%1 Stop Word(s) has been imported', $count));
            } else {
                $this->messageManager->addWarningMessage('Imported file was empty or all terms are already exist');
            }
        } else {
            $this->messageManager->addErrorMessage('There are no data to import');
            return $resultRedirect->setPath('*/*/import');
        }

        return $resultRedirect->setPath('*/*/');
    }
}
