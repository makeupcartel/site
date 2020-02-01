<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2016-04-18T18:33:28+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Profile/Edit/Tab/Output.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Profile\Edit\Tab;

class Output extends \Xtento\ProductExport\Block\Adminhtml\Widget\Tab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Xtento\ProductExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @var \Magento\Framework\View\Asset\Source
     */
    protected $assetSource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Xtento\ProductExport\Helper\Entity $entityHelper
     * @param \Magento\Framework\View\Asset\Source $assetSource
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Xtento\ProductExport\Helper\Entity $entityHelper,
        \Magento\Framework\View\Asset\Source $assetSource,
        array $data = []
    ) {
        $this->entityHelper = $entityHelper;
        $this->assetSource = $assetSource;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function getFormMessages()
    {
        $formMessages = [];
        $formMessages[] = [
            'type' => 'notice',
            'message' => __(
                'The XSL Template "translates" the internal Magento database format into your required output format. You can find more information about XSL Templates in our <a href="http://support.xtento.com/wiki/Magento_2_Extensions:Magento_Product_Export_Module" target="_blank">support wiki</a>. If you don\'t want to create the XSL Template yourself, please refer to our <a href="http://www.xtento.com/magento-services/xsl-template-creation-service.html" target="_blank">XSL Template Creation Service</a>.<br/>Looking for the ready-to-use integrations that come with the extension out of the box? Please check out the setup instructions <a href="http://support.xtento.com/wiki/Magento_2_Extensions:Magento_Product_Export_Module#Free_ready-to-use_XSL_Templates" target="_blank">here</a>.'
            )
        ];
        return $formMessages;
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $this->setForm($form);
        $this->setTemplate('Xtento_ProductExport::profile/output.phtml');
        return parent::_prepareForm();
    }

    public function getTestId()
    {
        $profile = $this->_coreRegistry->registry('productexport_profile');
        if (!$profile->getEntity()) {
            return '';
        }
        $testId = $profile->getTestId();
        if (!$testId || $testId == 0) {
            return $this->entityHelper->getLastEntityId($profile->getEntity());
        } else {
            return $testId;
        }
    }

    public function getXslTemplate()
    {
        return htmlspecialchars($this->_coreRegistry->registry('productexport_profile')->getXslTemplate(), ENT_NOQUOTES);
    }

    public function getRegistry()
    {
        return $this->_coreRegistry;
    }

    public function getJs($filename)
    {
        $url = $this->_assetRepo->createAsset(
            'Xtento_ProductExport::js/' . $filename,
            ['_secure' => $this->getRequest()->isSecure()]
        )->getUrl();
        return $url;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Output Format');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Output Format');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}