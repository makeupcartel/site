<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */
namespace Amasty\Oaction\Plugin\Ui\Model;

class Manager extends AbstractReader
{
    /*
     * Create xml config with php for enable\disable it from admin panel
     * */
    public function aroundGetData(
        \Magento\Ui\Model\Manager $subject,
        \Closure $proceed,
        $name
    ) {
        $result = $proceed($name);
        $availableActions = $this->helper->getModuleConfig('general/commands');
        $availableActions = explode(',', $availableActions);

        if (isset($result['sales_order_grid']['children']['listing_top']['children']['listing_massaction']['children'])
        ) {
            $children =
                &$result['sales_order_grid']['children']['listing_top']['children']['listing_massaction']['children'];
            foreach ($children as $item) {
                $actionName = $item['attributes']['name'];
                if (strpos($actionName, 'amasty_oaction')  === false || $actionName == 'amasty_oaction_delemiter') {
                    continue;
                }
                if ($actionName == 'amasty_oaction_status'
                    && isset($item['arguments']['actions']['item'])
                    && !isset($item['arguments']['actions']['item'][0]['item']['child'])
                ) {
                    $children[$actionName] = $this->_addStatusValues($item);
                }

                if (in_array($actionName, $availableActions)) {
                    /* compatibility with Amasty Order Attributes*/
                    if ($actionName == 'amasty_oaction_orderattr') {
                        if ($this->moduleManager->isEnabled('Amasty_Orderattr')) {
                            continue;
                        }
                    } else {
                        continue;
                    }
                }

                //remove if disabled
                unset($children[$actionName]);
            }
        }

        return $result;
    }
}
