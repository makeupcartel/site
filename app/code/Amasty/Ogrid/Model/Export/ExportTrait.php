<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Model\Export;

trait ExportTrait
{
    public function getDataProviderItems($dataProvider, $availableProductDetails)
    {
        $items = $dataProvider->getSearchResult()->getItems();

        $data = $dataProvider->getData();
        $dataItems = array_key_exists('items', $data) ? $data['items'] : [];

        foreach ($items as $idx => $item) {
            foreach ($dataItems as $dataItem) {
                if ($dataItem['entity_id'] == $item['entity_id']) {

                    if (array_key_exists('amasty_ogrid_items_ordered', $dataItem)) {
                        $productDetailsColumn = [];
                        foreach ($dataItem['amasty_ogrid_items_ordered'] as $productInfo) {
                            foreach ($productInfo as $key => $value) {
                                if (array_key_exists($key, $availableProductDetails) && $value && !is_array($value)) {
                                    $productDetailsColumn[] = $availableProductDetails[$key] . ':' . $value;
                                }
                            }
                        }
                        $dataItem['amasty_ogrid_items_ordered'] = implode('|', $productDetailsColumn);
                    }

                    if (array_key_exists('amasty_ogrid_sales_shipment_track', $dataItem)) {
                        $dataItem['amasty_ogrid_sales_shipment_track'] = implode(',', $dataItem['amasty_ogrid_sales_shipment_track']);
                    }

                    $item->setData(array_merge($item->getData(), $dataItem));

                    break;
                }
            }
        }

        return $items;
    }
}