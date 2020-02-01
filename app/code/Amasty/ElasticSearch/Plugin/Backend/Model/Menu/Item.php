<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\Backend\Model\Menu;

class Item
{
    /**
     * @param \Magento\Backend\Model\Menu\Item $subject
     * @param string $result
     * @return mixed
     */
    public function afterGetUrl(
        \Magento\Backend\Model\Menu\Item $subject,
        $result
    ) {
        /* hack for having correct url key ( we cant add params in menu.xml file)*/
        if ($subject->getId() == 'Amasty_ElasticSearch::settings') {
            $find = 'admin/system_config/edit';
            $result = str_replace($find, $find . '/section/amasty_elastic', $result);
        }

        return $result;
    }
}
