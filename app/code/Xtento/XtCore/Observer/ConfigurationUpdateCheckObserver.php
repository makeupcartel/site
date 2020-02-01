<?php

/**
 * Product:       Xtento_XtCore
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2017-08-16T08:52:13+00:00
 * File:          app/code/Xtento/XtCore/Observer/ConfigurationUpdateCheckObserver.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Observer;

class ConfigurationUpdateCheckObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Xtento\XtCore\Model\System\Config\Backend\Configuration
     */
    protected $configurationCheck;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Xtento\XtCore\Model\System\Config\Backend\Configuration $configurationCheck
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Xtento\XtCore\Model\System\Config\Backend\Configuration $configurationCheck,
        \Magento\Framework\Registry $registry
    ) {
        $this->configurationCheck = $configurationCheck;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $updatedConfiguration = $this->registry->registry('xtento_configuration_updated');
        if ($updatedConfiguration !== null) {
            $this->registry->unregister('xtento_configuration_updated');
            $this->configurationCheck->afterUpdate($updatedConfiguration);
        }
    }
}
