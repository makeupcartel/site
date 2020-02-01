<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\Grid\Export\Sales\Overview;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Ui\Model\Export\MetadataProvider as OriginalMetadataProvider;
use \Amasty\Reports\Model\ResourceModel\Sales\Overview\Grid\Collection as OverviewGridCollection;

/**
 * Class MetadataProvider
 */
class MetadataProvider extends OriginalMetadataProvider
{
    /**
     * @inheritdoc
     */
    public function getRowData(DocumentInterface $document, $fields, $options)
    {
        $this->addPersentColumns($document);

        return  parent::getRowData($document, $fields, $options);
    }

    /**
     * @param DocumentInterface $document
     */
    private function addPersentColumns($document)
    {
        $fields = [
            'total_orders', 'total_items', 'subtotal', 'tax', 'shipping', 'discounts', 'total', 'invoiced', 'refunded'
        ];
        foreach ($fields as $field) {
            $document->setData(
                'percent_' . $field,
                $this->getPercent($document->getData($field), $this->getTotals()[$field])
            );
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
     * @return array
     */
    public function getTotals()
    {
        /** @var \Magento\Framework\Api\Search\SearchResultInterface|OverviewGridCollection $collection */
        $collection = $this->filter->getComponent()->getContext()->getDataProvider()->getSearchResult();
        return $collection->getTotals();
    }
}
