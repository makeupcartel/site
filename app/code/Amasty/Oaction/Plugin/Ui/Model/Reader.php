<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Plugin\Ui\Model;

class Reader extends AbstractReader
{
    /**
     * Added statuses to change status action on Magento 2.2.x
     *
     * @param \Magento\Ui\Config\Reader $subject
     * @param array $result
     * @return array
     */
    public function afterRead(
        \Magento\Ui\Config\Reader $subject,
        $result
    ) {
        $availableActions = $this->helper->getModuleConfig('general/commands');
        $availableActions = explode(',', $availableActions);

        $isOrderGrid = false;

        if (isset($result['children']) && array_key_exists('sales_order_columns', $result['children'])) {
            $isOrderGrid = true;
        }

        if ($this->moduleManager->isOutputEnabled('Amasty_Oaction') &&
            isset($result['children']['listing_top']['children']['listing_massaction']['children']) &&
            $isOrderGrid
        ) {
            $children = &$result['children']['listing_top']['children']['listing_massaction']['children'];
            foreach ($children as $item) {
                $actionName = $item['attributes']['name'];
                if (strpos($actionName, 'amasty_oaction')  === false || $actionName == 'amasty_oaction_delemiter') {
                    continue;
                }
                if ($actionName == 'amasty_oaction_status') {
                    $children['amasty_oaction_status'] = $this->_addStatusValues($children['amasty_oaction_status']);
                }
                if (in_array($actionName, $availableActions)) {
                    continue;
                } else {
                    //remove if disabled
                    unset($children[$actionName]);
                }
            }
        }

        return $result;
    }
}
