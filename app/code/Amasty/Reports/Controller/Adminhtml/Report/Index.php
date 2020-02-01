<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Controller\Adminhtml\Report;

use Amasty\Reports\Controller\Adminhtml\Report as ReportController;
use Amasty\Reports\Model\Dashboard;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\RequestInterface;
use Amasty\Reports\Model\Grid\Bookmark;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Index
 * @package Amasty\Reports\Controller\Adminhtml\Report
 */
class Index extends ReportController
{
    /**
     * @var Dashboard
     */
    private $dashboardModel;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var \Amasty\Reports\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Amasty\Reports\Model\Widget
     */
    private $widgetModel;

    /**
     * @var \Amasty\Reports\Block\Adminhtml\Dashboard
     */
    private $dashboardBlock;

    /**
     * @var \Amasty\Reports\Model\ResourceModel\Sales\Overview\Collection
     */
    private $overviewCollection;

    /**
     * @var \Amasty\Reports\Model\ResourceModel\Sales\Overview\CollectionFactory
     */
    private $salesCollectionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $jsonFactory,
        RawFactory $resultRawFactory,
        \Amasty\Reports\Helper\Data $dataHelper,
        \Amasty\Reports\Block\Adminhtml\Dashboard $dashboardBlock,
        \Amasty\Reports\Model\ResourceModel\Sales\Overview\Collection $overviewCollection,
        \Amasty\Reports\Model\ResourceModel\Sales\Overview\CollectionFactory $salesCollectionFactory,
        Data $jsonHelper,
        Dashboard $dashboardModel,
        \Amasty\Reports\Model\Widget $widgetModel,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Registry $registry,
        Bookmark $bookmark,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context, $resultPageFactory, $resultRawFactory, $bookmark, $dataPersistor, $jsonFactory);
        $this->dashboardModel = $dashboardModel;
        $this->request = $context->getRequest();
        $this->jsonHelper = $jsonHelper;
        $this->jsonFactory = $jsonFactory;
        $this->dataHelper = $dataHelper;
        $this->widgetModel = $widgetModel;
        $this->dashboardBlock = $dashboardBlock;
        $this->overviewCollection = $overviewCollection;
        $this->salesCollectionFactory = $salesCollectionFactory;
        $this->moduleManager = $moduleManager;
        $this->registry = $registry;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_request->getActionName();
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $jsonFactory = $this->jsonFactory->create();

        if ($this->getRequest()->isAjax() && $this->isWrongValue()) {
            $jsonFactory->setData(['error' => __('Please enter a valid date.')]);

            return $jsonFactory;
        }

        if ($this->prepareDashboard($jsonFactory)) {
            return $jsonFactory;
        }

        $this->prepareResponse();

        $resultPage->setActiveMenu('Amasty_Reports::reports');
        $resultPage->addBreadcrumb(__('Advanced Reports'), __('Advanced Reports'));
        $resultPage->getConfig()->getTitle()->prepend(__('Advanced Reports'));

        return $resultPage;
    }

    /**
     * @param $jsonFactory
     * @return int
     */
    protected function prepareDashboard($jsonFactory)
    {
        $isDashboard = 0;
        if ($this->getRequest()->isAjax()) {
            switch ($this->getRequest()->getParam('amaction')) {
                case 'rate':
                case 'funnel':
                    $jsonFactory->setData($this->createFunnelData());
                    $isDashboard = 1;
                    break;
                case 'widget':
                    $data = $this->getRequest()->getParam('amreports');
                    $this->changeWidget($data['group'], $data['parent'] ?? 'total', $data['widget']);
                    $jsonFactory->setData($this->getWidgetData($data['group'], $data['widget']));
                    $isDashboard = 1;
                    break;
                case 'sales':
                    if ($storeId = $this->dataHelper->getCurrentStoreId()) {
                        $this->getRequest()->setParams(
                            ['amreports' => ['store' => $storeId]]
                        );
                    }
                    $collection = $this->overviewCollection->prepareCollection($this->salesCollectionFactory->create());
                    $jsonFactory->setData($collection->toArray());
                    $isDashboard = 1;
                    break;
            }
        }

        if ($this->getRequest()->isAjax()) {
            $isDashboard = 1;
        }

        if ($this->moduleManager->isEnabled('Amasty_Rolepermissions')) {
            $rule = $this->registry->registry('current_amrolepermissions_rule');
            if (isset($rule) && $scopes = $rule->getScopeStoreviews()) {
                $this->dataHelper->setCurrentStore($scopes[0]);
            }
        }

        return $isDashboard;
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
     * @return string
     */
    protected function createFunnelData()
    {
        list($from, $to) = $this->getFromTo();

        return $this->jsonHelper->jsonEncode($this->dashboardModel->getConversionFunnel($from, $to));
    }

    /**
     * @return array
     */
    private function getFromTo()
    {
        $filters = $this->request->getParam('amreports');
        $from = isset($filters['funnel_from']) ? $filters['funnel_from'] : null;
        $to = isset($filters['funnel_to']) ? $filters['funnel_to'] : null;

        return [$from, $to];
    }

    /**
     * @param $group
     * @param $number
     * @param $name
     */
    protected function changeWidget($group, $number, $name)
    {
        $this->widgetModel->changeWidget($group, $number, $name);
    }

    /**
     * @param $group
     * @param $name
     * @return mixed
     */
    protected function getWidgetData($group, $name)
    {
        $value = $this->widgetModel->getWidgetData($name);
        $allWidgets = $this->widgetModel->getWidgets($group);
        $allWidgets[$name]['value'] = $value;
        if (isset($allWidgets[$name]['link'])) {
            $allWidgets[$name]['link'] = $this->dashboardBlock->getUrl($allWidgets[$name]['link']);
        }
        return $allWidgets[$name];
    }
}
