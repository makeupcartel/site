<?php

namespace Convert\SkinQuiz\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Convert\SkinQuiz\Model\Export\CsvExport as CsvExportModel;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class Csvexport extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    protected $_resultPageFactory;
        /**
     * @var ConvertToCsv
     */
        protected $converter;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context, 
        PageFactory $pageFactory,
        CsvExportModel $converter,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        Filter $filter,
        LoggerInterface $logger
    )
    {
        parent::__construct($context);
        $this->_resultPageFactory = $pageFactory;
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
        $this->filter = $filter ?: ObjectManager::getInstance()->get(Filter::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    public function execute()
    {
        return $this->fileFactory->create('export.csv', $this->converter->getFileCsv(), 'var');
    }

}
