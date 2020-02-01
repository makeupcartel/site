<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */

namespace Amasty\Ogrid\Ui\Component\Listing\Column;

class OrderItemsSku extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key => &$item) {
                if (isset($item['amasty_ogrid_items_sku'])) {
                    $skus = explode(',', $item['amasty_ogrid_items_sku']);
                    $item['amasty_ogrid_items_sku'] = '';
                    
                    foreach ($skus as $sku) {
                        $item['amasty_ogrid_items_sku'] .= '<p>' . $sku . '</p>';
                    }
                }
            }
        }

        return $dataSource;
    }
}
