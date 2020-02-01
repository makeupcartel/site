<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Amasty\Reports\Model\Grid\Bookmark;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Report
 * @package Amasty\Reports\Controller\Adminhtml
 */
abstract class Report extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Bookmark
     */
    protected $bookmark;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        Bookmark $bookmark,
        DataPersistorInterface $dataPersistor,
        JsonFactory $jsonFactory
    ) {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->bookmark = $bookmark;
        $this->dataPersistor = $dataPersistor;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function prepareResponse()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        if ($this->getRequest()->isAjax()) {
            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();

            if ($this->isWrongValue()) {
                $jsonFactory = $this->jsonFactory->create();
                $jsonFactory->setData(['error' => __('Please enter a valid date.')]);

                return $jsonFactory;
            }

            $rawContent = $resultPage->getLayout()->renderElement('amreports.report.content');
            $resultRaw->setContents($rawContent);

            return $resultRaw;
        }

        $resultPage->setActiveMenu('Amasty_Reports::reports');
        $resultPage->addBreadcrumb(__('Advanced Reports'), __('Advanced Reports'));
        $resultPage->getConfig()->getTitle()->prepend(__('Advanced Reports'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    public function isWrongValue()
    {
        $params = $this->getRequest()->getParam('amreports');
        $wrongValue = false;
        foreach ($params as $key => $param) {
            if ((strpos($key, 'from') !== false || ($key == 'to' || strpos($key, 'to_') !== false))
                && $param
                && !strtotime($param)) {
                $wrongValue = true;
                break;
            }
        }

        return $wrongValue;
    }

    /**
     * @param array $params
     */
    public function injectFilters($namespace, $params)
    {
        $this->bookmark->applyFilter(
            $namespace,
            $params
        );
        $this->bookmark->clear();
    }
}
