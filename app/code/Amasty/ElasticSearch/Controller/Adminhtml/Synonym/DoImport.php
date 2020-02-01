<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Controller\Adminhtml\Synonym;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Amasty\ElasticSearch\Api\Data\SynonymInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Amasty\ElasticSearch\Model\Indexer\Data\DataMapperResolver;

class DoImport extends Action
{
    const ADMIN_RESOURCE = 'Amasty_ElasticSearch::synonym';
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
     * @var \Amasty\ElasticSearch\Model\SynonymRepository
     */
    private $synonymRepository;

    /**
     * @var \Amasty\ElasticSearch\Model\SynonymFactory
     */
    private $synonymFactory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    private $csv;

    /**
     * @var \Magento\Indexer\Model\IndexerFactory
     */
    protected $indexerFactory;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Amasty\ElasticSearch\Model\SynonymRepository $synonymRepository,
        \Amasty\ElasticSearch\Model\SynonymFactory $synonymFactory,
        \Magento\Framework\File\Csv $csv,
        \Magento\Indexer\Model\IndexerFactory $indexerFactory
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->ioFile = $ioFile;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->synonymRepository = $synonymRepository;
        $this->synonymFactory = $synonymFactory;
        $this->csv = $csv;
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPostValue();
        $storeId = (int) $this->getRequest()->getParam(SynonymInterface::STORE_ID);
        $count = 0;

        if ($data) {
            try {
                $file = $this->uploadFile();
                $csvData = $this->csv->getData($file);

                foreach ($csvData as $data) {
                    $term = ($data && is_array($data)) ? implode(',', $data) : null;
                    if ($term) {
                        try {
                            $model = $this->synonymFactory->create()
                                ->setStoreId($storeId)
                                ->setTerm($term);
                            $this->synonymRepository->save($model);
                        } catch (AlreadyExistsException $ex) {
                            continue;
                        } catch (CouldNotSaveException $ex) {
                            continue;
                        }
                        $count++;
                    }
                }
                $this->invalidateIndex();
                $this->ioFile->rm($file);
            } catch (LocalizedException $ex) {
                $this->messageManager->addErrorMessage($ex->getMessage());
                return $resultRedirect->setPath('*/*/import');
            } catch (\Exception $ex) {
                $this->messageManager->addErrorMessage(__('Something went wrong. Please try again'));
                return $resultRedirect->setPath('*/*/import');
            }

            if ($count > 0) {
                $this->messageManager->addSuccessMessage(__('%1 Synonym(s) has been imported', $count));
            } else {
                $this->messageManager->addWarningMessage('Imported file was empty or all terms are already exist');
            }
        } else {
            $this->messageManager->addErrorMessage('There are no data to import');
            return $resultRedirect->setPath('*/*/import');
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws \Exception
     */
    private function uploadFile()
    {
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

        return $file;
    }

    private function invalidateIndex()
    {
        $indexer = $this->indexerFactory->create()->load(DataMapperResolver::DEFAULT_DATA_INDEXER);
        $indexer->invalidate();
    }
}
