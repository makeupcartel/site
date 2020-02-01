<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-14T15:37:35+00:00
 * File:          app/code/Xtento/ProductExport/Model/System/Config/Source/Export/Events.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\System\Config\Source\Export;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class Events implements ArrayInterface
{
    /**
     * @var \Xtento\ProductExport\Observer\AbstractEventObserver
     */
    protected $eventObserver;

    /**
     * Events constructor.
     * @param \Xtento\ProductExport\Observer\AbstractEventObserver $eventObserver
     */
    public function __construct(\Xtento\ProductExport\Observer\AbstractEventObserver $eventObserver)
    {
        $this->eventObserver = $eventObserver;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray($entity = false)
    {
        $optionArray = [];
        $events = $this->eventObserver->getEvents($entity);
        foreach ($events as $entityEvents) {
            foreach ($entityEvents as $eventId => $eventOptions) {
                $optionArray[$eventId] = $eventOptions['label'];
            }
        }
        return $optionArray;
    }
}
