<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2017-04-27T19:38:38+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Profile/FieldsXml.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Profile;

class FieldsXml extends \Xtento\ProductExport\Controller\Adminhtml\Profile
{
    /**
     * @var \Xtento\ProductExport\Model\Output\XmlFactory
     */
    protected $outputXmlFactory;

    /**
     * FieldsXml constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Xtento\ProductExport\Helper\Entity $entityHelper
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\Output\XmlFactory $outputXmlFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Xtento\ProductExport\Model\ResourceModel\Profile\CollectionFactory $profileCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Xtento\ProductExport\Helper\Entity $entityHelper,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Model\Output\XmlFactory $outputXmlFactory
    ) {
        parent::__construct(
            $context,
            $moduleHelper,
            $cronHelper,
            $profileCollectionFactory,
            $registry,
            $escaper,
            $scopeConfig,
            $dateFilter,
            $entityHelper,
            $profileFactory
        );
        $this->outputXmlFactory = $outputXmlFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('profile_id');
        $model = $this->profileFactory->create()->load($id);
        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This profile no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
            );
            return $resultRedirect->setPath('*/*/');
        }
        $this->registry->unregister('productexport_profile');
        $this->registry->register('productexport_profile', $model);

        $export = $this->_objectManager->create(
            '\Xtento\ProductExport\Model\Export\Entity\\' . ucfirst($model->getEntity())
        );
        $export->setProfile($model);
        $export->setShowEmptyFields(1);
        $filterField = $model->getEntity() == \Xtento\ProductExport\Model\Export::ENTITY_REVIEW ? 'main_table.review_id': 'entity_id';
        $export->setCollectionFilters(
            [[$filterField => ['in' => explode(",", $this->getRequest()->getParam('test_id'))]]]
        );
        $returnArray = $export->runExport();
        $xmlData = $this->outputXmlFactory->create()->setProfile($model)->convertData($returnArray);

        if (empty($xmlData)) {
            $xmlData[0] = '<objects></objects>';
        }
        /** @var \Magento\Framework\Controller\Result\Raw $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $resultPage->setHeader('Content-Type', 'text/xml');
        $resultPage->setContents($xmlData[0]);
        return $resultPage;
    }
}
