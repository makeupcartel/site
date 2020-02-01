<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml;

use Amasty\Reports\Helper\Data;
use Amasty\Reports\Model\ResourceModel\Sales\Overview\Collection;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\AbstractForm;
use Amasty\Reports\Block\Adminhtml\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\System\Store;

/**
 * Class Dashboard
 * @package Amasty\Reports\Block\Adminhtml
 */
class Dashboard extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Amasty\Reports\Model\Dasboard
     */
    private $dashboardModel;

    /**
     * @var Store
     */
    private $systemStore;

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var Collection
     */
    private $overviewCollection;

    /**
     * @var \Amasty\Reports\Model\ResourceModel\Sales\Overview\CollectionFactory
     */
    private $salesCollectionFactory;

    /**
     * @var \Amasty\Reports\Model\Widget
     */
    private $widgetModel;

    public function __construct(
        Context $context,
        \Amasty\Reports\Model\Dashboard $dashboardModel,
        Collection $overviewCollection,
        \Amasty\Reports\Model\ResourceModel\Sales\Overview\CollectionFactory $salesCollectionFactory,
        \Amasty\Reports\Model\Widget $widgetModel,
        Data $dataHelper,
        Registry $registry,
        FormFactory $formFactory,
        Store $systemStore,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Data\FormFactory $magentoFormFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $registry, $magentoFormFactory, $data);
        $this->dashboardModel = $dashboardModel;
        $this->systemStore = $systemStore;
        $this->formFactory = $formFactory;
        $this->request = $context->getRequest();
        $this->dataHelper = $dataHelper;
        $this->overviewCollection = $overviewCollection;
        $this->salesCollectionFactory = $salesCollectionFactory;
        $this->widgetModel = $widgetModel;
    }
    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formFactory->create([
            'data' => [
                'id' => 'report_toolbar',
                'action' => $this->_urlBuilder->getUrl('amasty_reports/report/index', [
                    'store' => (int) $this->dataHelper->getCurrentStoreId()
                ]),
            ]
        ]);

        $this->addControls($form);

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return \Amasty\Reports\Model\Dashboard
     */
    public function getLastOrders()
    {
        return $this->dashboardModel->getLastOrders();
    }

    /**
     * @return mixed
     */
    public function getConversionFunnel()
    {
        $from = date('Y-m-d', $this->dataHelper->getDefaultFromDate());
        $to = date('Y-m-d', $this->dataHelper->getDefaultToDate());
        return $this->dashboardModel->getConversioFnFunnel($from, $to);
    }

    /**
     * @return false|string
     */
    public function getFromDate()
    {
        return date('Y-m-d', $this->dataHelper->getDefaultFromDate());
    }

    /**
     * @return false|string
     */
    public function getToDate()
    {
        return date('Y-m-d', $this->dataHelper->getDefaultToDate());
    }

    /**
     * @return false|string
     */
    public function getMonthFromDate()
    {
        return date('Y-m-d', strtotime('-1 month'));
    }

    /**
     * @return \Amasty\Reports\Model\ResourceModel\Sales\Overview\Grid\Collection
     */
    public function getSalesCollection()
    {
        if ($storeId = $this->dataHelper->getCurrentStoreId()) {
            $this->getRequest()->setParams(
                ['amreports' => ['store' => $storeId]]
            );
        }
        return $this->overviewCollection->prepareCollection($this->salesCollectionFactory->create());
    }

    /**
     * @param $widget
     * @return float|int|string
     */
    public function getWidgetsData($widget)
    {
        return $this->widgetModel->getWidgetData($widget);
    }

    /**
     * @param string $group
     *
     * @return array
     */
    public function getCurrentWidgets($group)
    {
        return $this->widgetModel->getCurrentWidgets($group);
    }

    /**
     * @param string $group
     *
     * @return array
     */
    public function getAllWidgets($group)
    {
        return $this->widgetModel->getWidgets($group);
    }

    /**
     * @return array
     */
    public function getWidgetGroups()
    {
        return $this->widgetModel->getWidgetGroups();
    }

    /**
     * @return mixed
     */
    public function getBestsellers()
    {
        $this->getRequest()->setParams(
            ['amreports' => ['to' => $this->getToDate(), 'from' => $this->getMonthFromDate()]]
        );
        return $this->dashboardModel->getBestsellers();
    }

    /**
     * @param AbstractForm $form
     * @return $this
     */
    protected function addControls(AbstractForm $form)
    {
        $form->addField('store', 'select', [
            'name' => 'store',
            'values' => $this->systemStore->getStoreValuesForForm(false, true),
            'wrapper_class' => 'amreports-select-block amreports-select-dashboard',
            'class' => 'amreports-select',
            'no_span' => true,
            'value' => $this->dataHelper->getCurrentStoreId()
        ]);

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalOrders()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getTotalSales()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getTotalRevenue()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getTotalCustomers()
    {
        return 0;
    }

    /**
     * @param $order
     * @return \Magento\Framework\Phrase|string
     */
    public function getOrderCustomerName($order)
    {
        $firstName = $order->getCustomerFirstname();
        $lastName = $order->getCustomerLastname();

        return $firstName || $lastName ? trim($firstName . ' ' . $lastName) : __('NOT LOGGED IN');
    }

    /**
     * @param $price
     *
     * @return float
     */
    public function formatPrice($price)
    {
        return $this->dataHelper->convertPrice($price);
    }

    /**
     * @return string
     */
    public function getConversionReportUrl()
    {
        return $this->_urlBuilder->getUrl('amasty_reports/report_customers/conversionRate');
    }
}
