<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Oaction
 */


namespace Amasty\Oaction\Plugin\Ui\Model;

class AbstractReader
{
    /**
     * @var \Amasty\Oaction\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    /**
     * @var \Amasty\Oaction\Model\Source\State
     */
    protected $orderStatus;

    public function __construct(
        \Amasty\Oaction\Helper\Data $helper,
        \Magento\Framework\Module\Manager $moduleManager,
        \Amasty\Oaction\Model\Source\State $orderStatus
    ) {
        $this->helper = $helper;
        $this->moduleManager = $moduleManager;
        $this->orderStatus = $orderStatus;
    }

    /**
    * Added statuses to change status action
    */
    protected function _addStatusValues($item)
    {
        $childItem = [];
        $i = 0;
        $statusArray = explode(',' ,$this->helper->getModuleConfig('status/exclude_statuses'));
        $statuses = $this->orderStatus->toOptionArray();
        array_unshift($statuses, [
            'value' => '',
            'label' => __('Magento default')->__toString()
        ]);
        foreach ($statuses as $status) {
            if (!in_array($status['value'], $statusArray) || $status['value'] == '') {
                $childItem[] = [
                    "name" => (string) $i++,
                    "xsi:type" => "array",
                    "item" => [
                        "label" => [
                            "name" => "label",
                            "xsi:type" => "string",
                            "value" => $status['label']
                        ],
                        "fieldvalue" => [
                            "name" => "fieldvalue",
                            "xsi:type" => "string",
                            "value" => $status['value']
                        ],
                    ]
                ];
            }
        }

        $item['arguments']['actions']['item'][0]['item']['child'] = [
            "name" => "child",
            "xsi:type" => "array",
            'item' => $childItem
        ];

        return $item;
    }
}
