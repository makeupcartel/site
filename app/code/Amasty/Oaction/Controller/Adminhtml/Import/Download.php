<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */
namespace Amasty\Oaction\Controller\Adminhtml\Import;

use Magento\Framework\Controller\ResultFactory;

class Download extends \Amasty\Oaction\Controller\Adminhtml\Import
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $reader;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $readFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * Download constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->reader = $reader;
        $this->readFactory = $readFactory;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $fileName = 'amasty_oaction.csv';
        $moduleDir = $this->reader->getModuleDir('', 'Amasty_Oaction');
        $fileAbsolutePath = $moduleDir . '/Files/Sample/' . $fileName;
        $directoryRead = $this->readFactory->create($moduleDir);
        $filePath = $directoryRead->getRelativePath($fileAbsolutePath);
        if (!$directoryRead->isFile($filePath)) {
            $this->messageManager->addErrorMessage(__('There is no sample file for this entity.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        $directoryRead->stat($filePath)['size'];
        $this->fileFactory->create(
            $fileName,
            null,
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream',
            $directoryRead->stat($filePath)['size']
        );
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setContents($directoryRead->readFile($filePath));
        return $resultRaw;
    }
}
