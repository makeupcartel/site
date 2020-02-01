<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-18T18:20:05+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Condition/Combine.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Condition;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Combine constructor.
     *
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->eventManager = $eventManager;
        $this->registry = $registry;
        parent::__construct($context, $data);
        $this->setType('Xtento\ProductExport\Model\Export\Condition\Combine');
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Xtento\ProductExport\Model\Export\Condition\Product\Found',
                    'label' => __('Product attribute combination')
                ]
            ]
        );

        $additional = new \Magento\Framework\DataObject();
        $this->eventManager->dispatch('xtento_productexport_rule_condition_combine', ['additional' => $additional]);
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
}
