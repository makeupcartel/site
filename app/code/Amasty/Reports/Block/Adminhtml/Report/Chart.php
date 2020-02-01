<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Block\Adminhtml\Report;

use Magento\Backend\Block\Template\Context;
use Amasty\Reports\Block\Adminhtml\Report\Sales\Overview\Compare\Toolbar as OverviewToolbar;

/**
 * Class Chart
 */
class Chart extends \Magento\Backend\Block\Template
{
    /**
     * @var $collectionObject
     */
    private $collectionObject;

    /**
     * @var array
     */
    private $lineNumbers;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    private $currency;

    public function __construct(
        Context $context,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currency = $currency;
        if (isset($data['collection'])) {
            $this->collectionObject = $data['collection'];
        } else {
            $this->collectionObject = isset($data['collectionFactory']) ? $data['collectionFactory'] : null;
        }
        
        if (!$this->collectionObject) {
            throw new \Magento\Framework\Webapi\Exception(__('Collection is not specified for chart block'));
        }
    }

    /**
     * @return $collection
     */
    public function getCollection()
    {
        $this->collectionObject->setFlag('force_sorting', true);
        $this->collectionObject->prepareCollection($this->collectionObject);

        return $this->collectionObject;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getYAxisName()
    {
        $params = $this->getRequest()->getParam('amreports');

        return $params && isset($params['value']) && $params['value'] == 'total' ? __('Total') : __('Qty');
    }

    /**
     * @return bool
     */
    public function getInterval()
    {
        $params = $this->getRequest()->getParam('amreports');

        return $params && isset($params['interval']) ? $params['interval'] : 0;
    }

    /**
     * @return array
     */
    public function getDataArray()
    {
        $params = $this->getRequest()->getParam('amreports');
        $resultData = [];
        if ($params) {
            $params = array_diff($params, ['']);
            $collections = $this->getCollections($params);

            $dates = $this->getAllDates($collections);
            $resultData = $this->getResultArrayForSeveralValues($collections, array_unique($dates));
        } else {
            $collection = $this->collectionObject->create()->prepareCollection();
            foreach ($collection->getItems() as $item) {
                $this->lineNumbers[0] = 0;
                $resultData[] = [
                    'date' => $item->getCreatedAt(),
                    'orders_0' => $item->getTotal()
                ];
            }
        }

        return json_encode($resultData);
    }

    /**
     * @return array
     */
    public function getLineNumbers()
    {
        return $this->lineNumbers;
    }

    /**
     * @param $params
     * @return array
     */
    private function getCollections($params)
    {
        $fromValues = [];
        $toValues = [];
        foreach ($params as $key => $param) {
            if (strpos($key, 'from_') !== false) {
                $fromValues[] = $param;
            }
            if (strpos($key, 'to_') !== false) {
                $toValues[] = $param;
            }
        }

        $collections = [];
        $paramsCount = count($fromValues);
        for ($index = 0; $index < $paramsCount; $index++) {
            if (isset($toValues[$index])) {
                $collection = $this->collectionObject->create();
                $collection->prepareCollection($fromValues[$index], $toValues[$index]);
                $collections[] = $collection;
            }
        }

        return $collections;
    }

    /**
     * @param $collections
     * @return array
     */
    private function getAllDates($collections)
    {
        $dates = [];
        foreach ($collections as $collection) {
            foreach ($collection->getItems() as $item) {
                $dates[] = $item->getCreatedAt();
            }
        }
        usort($dates, [$this, 'sortByDate']);

        return $dates;
    }

    /**
     * @param $collections
     * @param $dates
     * @return mixed
     */
    private function getResultArrayForSeveralValues($collections, $dates)
    {
        $resultData = [];
        foreach ($collections as $key => $collection) {
            $index = 0;
            foreach ($collection->getItems() as $item) {
                $this->lineNumbers[$key] = $key;
                if (isset($resultData[$index])) {
                    $resultData[$index]['orders_' . $key] = $item->getTotal();
                } else {
                    $resultData[$index] = [
                        'date' => array_slice($dates, $index, 1),
                        'orders_' . $key => $item->getTotal()
                    ];
                }
                $index++;
            }
        }

        return $resultData;
    }

    /**
     * @param $first
     * @param $second
     * @return false|int
     */
    public function sortByDate($first, $second)
    {
        return strtotime($first) - strtotime($second);
    }

    /**
     * @return array
     */
    public function getAxisFields()
    {
        $x = 'total_orders';
        $y = 'period';
        $filters = $this->getRequest()->getParam('amreports');
        $group = isset($filters['type']) ? $filters['type'] : 'overview';
        switch ($group) {
            case 'overview':
                $y = 'period';
                break;
            case 'status':
                $y = 'status';
                break;
        }

        $group = isset($filters['value']) ? $filters['value'] : 'quantity';
        switch ($group) {
            case 'quantity':
                $x = 'total_orders';
                break;
            case 'total':
                $x = 'total';
                break;
        }

        return ['x' => $x, 'y' => $y];
    }

    /**
     * @return string
     */
    public function getValueType()
    {
        $filters = $this->getRequest()->getParam('amreports');

        return isset($filters['value']) ? $filters['value'] : 'qty';
    }

    /**
     * @return string
     */
    public function getCurrencySymbol($type = null)
    {
        $code = $this->currency->getConfigDefaultCurrencies();
        $this->currency->load($code[0]);
        $type = $type ?: $this->getValueType();
        return $type == 'total' ? $this->currency->getCurrencySymbol() : '';
    }

    /**
     * @return bool
     */
    public function isDate()
    {
        $filters = $this->getRequest()->getParam('amreports');
        $group = isset($filters['type']) ? $filters['type'] : 'overview';
        $group == 'status' ? $isDate = false : $isDate = true;
        return $isDate;
    }

    /**
     * @param $key
     * @return string
     */
    public function getColor($key)
    {
        $colors = [
            OverviewToolbar::COLOR_FIRST_LINE,
            OverviewToolbar::COLOR_SECOND_LINE,
            OverviewToolbar::COLOR_THIRD_LINE,
        ];

        return $colors[$key];
    }

    /**
     * @return string
     */
    public function isDateInterval()
    {
        return $this->getCollection()->getFlag('interval') == 'day' ? 'true' : 'false';
    }

    /**
     * @param string $defaultValue
     * @return string
     */
    public function getWidgetName($defaultValue)
    {
        $value = $defaultValue;
        $params = $this->getRequest()->getParam('amreports');

        if (isset($params['view_type'])) {
            switch ($params['view_type']) {
                case 'line':
                    $value = 'amreports_linear_charts';
                    break;
                case 'column':
                    $value = 'amreports_simple_column_chart';
                    break;
                case 'pie':
                    $value = 'amreports_simple_pie_chart';
                    break;
                case 'multi-linear':
                    $value = 'amreports_multi_linear_chart';
                    break;
                case 'multi-column':
                    $value = 'amreports_multi_column_chart';
                    break;
                case 'horizontal-column':
                    $value = 'amreports_horizontal_column_chart';
                    break;
            }
        }

        return $value;
    }
}
