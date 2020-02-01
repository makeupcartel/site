<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-27T12:30:21+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Action.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml;

abstract class Action extends \Magento\Backend\App\Action
{
    /**
     * @var \Xtento\ProductExport\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Xtento\XtCore\Helper\Cron
     */
    protected $cronHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory
     */
    protected $profileCollectionFactory;

    /**
     * Action constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->moduleHelper = $moduleHelper;
        $this->cronHelper = $cronHelper;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    protected function healthCheck()
    {
        // Has the module been installed properly?
        if (!$this->moduleHelper->isModuleProperlyInstalled()) {
            if ($this->getRequest()->getActionName() !== 'installation') {
                return '*/index/installation';
            } else {
                return true;
            }
        } else {
            if ($this->getRequest()->getActionName() == 'installation') {
                return '*/profile/index';
            }
        }
        // Check module status
        if (!$this->moduleHelper->confirmEnabled(true) || !$this->moduleHelper->isModuleEnabled()) {
            if ($this->getRequest()->getActionName() !== 'disabled') {
                return '*/index/disabled';
            }
        } else {
            if ($this->getRequest()->getActionName() == 'disabled') {
                return '*/profile/index';
            }
        }
        if ($this->getRequest()->getActionName() !== 'redirect') {
            // Check XSL status
            if (!class_exists('XSLTProcessor')) {
                $this->addWarning(
                    __(
                        'The XSLTProcessor class could not be found. This means your PHP installation is missing XSL features. You cannot export output formats using XSL Templates without the PHP XSL extension. Please get in touch with your hoster or server administrator to add XSL to your PHP configuration.'
                    )
                );

            }
            // Check if this module was made for the edition (CE/PE/EE) it's being run in
            if ($this->moduleHelper->isWrongEdition()) {
                $this->addError(
                    __(
                        'Attention: The installed extension version is not compatible with the Enterprise Edition of Magento. The compatibility of the currently installed extension version has only been confirmed with the Community Edition of Magento. Please go to <a href="https://www.xtento.com" target="_blank">www.xtento.com</a> to purchase or download the Enterprise Edition of this extension in our store if you\'ve already purchased it.'
                    )
                );
            }
            // Check cronjob status
            if (!$this->scopeConfig->isSetFlag('productexport/general/disable_cron_warning')) {
                $profileCollection = $this->profileCollectionFactory->create();
                $profileCollection->addFieldToFilter('enabled', 1); // Profile enabled
                $profileCollection->addFieldToFilter('cronjob_enabled', 1); // Cronjob enabled
                if ($profileCollection->getSize() > 0) {
                    if (!$this->cronHelper->isCronRunning()) {
                        if ((time() - $this->cronHelper->getInstallationDate()) > (60 * 30)) {
                            // Module was not installed within the last 30 minutes
                            if ($this->cronHelper->getLastCronExecution() == '') {
                                $this->addWarning(
                                    __(
                                        'Cronjob status: Cron doesn\'t seem to be set up at all. Cron did not execute within the last 15 minutes. Please make sure to set up the cronjob as explained <a href="http://support.xtento.com/wiki/Setting_up_the_Magento_cronjob_(Magento_2)" target="_blank">here</a> and check the cron status 15 minutes after setting up the cronjob properly again.'
                                    )
                                );
                            } else {
                                $this->addWarning(
                                    __(
                                        'Cronjob status: Cron doesn\'t seem to be set up properly. Cron did not execute within the last 15 minutes. Please make sure to set up the cronjob as explained <a href="http://support.xtento.com/wiki/Setting_up_the_Magento_cronjob_(Magento_2)" target="_blank">here</a> and check the cron status 15 minutes after setting up the cronjob properly again.'
                                    )
                                );
                            }
                        } // else: Cron status wasn't checked yet. Please check back in 30 minutes.
                    }
                }
            }
        }
        return true;
    }

    protected function addWarning($messageText)
    {
        return $this->addMsg('warning', $messageText);
    }

    protected function addError($messageText)
    {
        return $this->addMsg('error', $messageText);
    }

    protected function addMsg($type, $messageText)
    {
        $messages = $this->messageManager->getMessages();
        foreach ($messages->getItems() as $message) {
            if ($message->getText() == $messageText) {
                return false;
            }
        }
        return ($type === 'error') ?
            $this->messageManager->addComplexErrorMessage(
                'backendHtmlMessage',
                [
                    'html' => (string)$messageText
                ]
            ) :
            $this->messageManager->addComplexWarningMessage(
                'backendHtmlMessage',
                [
                    'html' => (string)$messageText
                ]
            );
    }

}