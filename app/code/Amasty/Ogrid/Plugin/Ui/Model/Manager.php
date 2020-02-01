<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


namespace Amasty\Ogrid\Plugin\Ui\Model;

class Manager extends AbstractReader
{
    /**
     * Added settings for order grid on magento 2.1.x
     *
     * @param \Magento\Ui\Model\Manager $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetData(
        \Magento\Ui\Model\Manager $subject,
        $result
    ) {
        if (isset($result['sales_order_grid']['children'])) {
            $result['sales_order_grid']['children'] = $this->addAmastySettings($result['sales_order_grid']['children']);
        }

        return $result;
    }
}
