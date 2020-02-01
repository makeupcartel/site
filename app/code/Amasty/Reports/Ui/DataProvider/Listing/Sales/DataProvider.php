<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Ui\DataProvider\Listing\Sales;

/**
 * Class DataProvider
 * @package Amasty\Reports\Ui\DataProvider\Listing\Sales
 */
class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @return array
     */
    public function getData()
    {
        $result = parent::getData();
        /** @var \Amasty\Reports\Model\ResourceModel\Sales\Overview\Grid\Collection $collection */
        $collection = $this->getSearchResult();
        $result['totals'] = $collection->getTotals();
        $this->addPersentColumns($result);

        return $result;
    }

    /**
     * @param $result
     */
    private function addPersentColumns(&$result)
    {
        $fields = [
            'total_orders', 'total_items', 'subtotal', 'tax', 'shipping', 'discounts', 'total', 'invoiced', 'refunded'
        ];
        foreach ($result['items'] as &$item) {
            foreach ($fields as $field) {
                $item['percent_' . $field] = $this->getPercent($item[$field], $result['totals'][$field]);
                $result['totals']['percent_' . $field] = (int) $result['totals'][$field] ? '100%' : __('N/A');
            }
        }
    }

    /**
     * @param $rowValue
     * @param $total
     * @return string
     */
    private function getPercent($rowValue, $total)
    {
        return $total != 0 ? round($rowValue / $total * 100, 2) . '%' : 0;
    }

    /**
     * @inheritdoc
     */
    public function addOrder($field, $direction)
    {
        $field = preg_replace(
            '/^percent_*/',
            '',
            $field
        );
        parent::addOrder($field, $direction);
    }
}
