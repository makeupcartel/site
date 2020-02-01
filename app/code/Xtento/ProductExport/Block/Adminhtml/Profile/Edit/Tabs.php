<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-29T13:35:26+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Profile/Edit/Tabs.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Profile\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Xtento\ProductExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\ProductExport\Helper\Entity $entityHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        \Xtento\ProductExport\Helper\Entity $entityHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->entityHelper = $entityHelper;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('profile_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Export Profile'));
        if ($this->registry->registry('productexport_profile')->getId()) {
            $this->setTitle(
                __(
                    '%1 Export Profile',
                    $this->entityHelper->getEntityName($this->registry->registry('productexport_profile')->getEntity())
                )
            );
        } else {
            $this->setTitle(__('Export Profile'));
        }
    }

    public function addTab($tabId, $tab)
    {
        if ($tabId == 'general' || $this->registry->registry('productexport_profile')->getId()) {
            if ($tabId == 'categories' && $this->_scopeConfig->isSetFlag('productexport/advanced/disable_category_mapper')) {
                return $this; // Disable category mapper
            }
            return parent::addTab($tabId, $tab);
        } else {
            return $this;
        }
    }
}