<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Ogrid
 */


namespace Amasty\Ogrid\Plugin\Ui\Model;

class Reader extends AbstractReader
{
    /**
     * Added settings for order grid on magento 2.2.x
     *
     * @param \Magento\Ui\Config\Reader $subject
     * @param array $result
     *
     * @return array
     */
    public function afterRead(
        \Magento\Ui\Config\Reader $subject,
        $result
    ) {
        if (isset($result['children']['sales_order_columns'])) {
            $result['children'] = $this->addAmastySettings($result['children']);
        }

        return $result;
    }
}
