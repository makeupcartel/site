<?php

namespace Convert\CustomerContent\Controller\Content;

use Magento\Framework\App\Action\Context;

class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_downloader;

    /**
     * @var Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;

    /**
     * Download constructor.
     * @param Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem\DirectoryList $directory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\DirectoryList $directory
    ) {
        $this->_downloader =  $fileFactory;
        $this->directory = $directory;
        parent::__construct($context);
    }

    public function execute()
    {
        $fileName = $this->getRequest()->getParam('file_name');
        $file = $this->getRequest()->getParam('file_path');
        // do your validations

        /**
         * do file download
         */
        return $this->_downloader->create(
            $fileName,
            @file_get_contents($file)
        );
    }
}