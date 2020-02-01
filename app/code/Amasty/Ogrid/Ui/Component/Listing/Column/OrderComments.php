<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


namespace Amasty\Ogrid\Ui\Component\Listing\Column;

class OrderComments extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key => &$item) {
                if (isset($item['amasty_ogrid_order_comments'])) {
                    $comments = explode(',', $item['amasty_ogrid_order_comments']);
                    $item['amasty_ogrid_order_comments'] = '';
                    foreach ($comments as $comment) {
                        $item['amasty_ogrid_order_comments'] .= '<p>' . $comment . '</p>';
                    }
                }
            }
        }
        return $dataSource;
    }
}
