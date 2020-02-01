<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-09-29T14:03:08+00:00
 * File:          app/code/Xtento/ProductExport/Controller/Adminhtml/Profile/SearchTaxonomy.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Controller\Adminhtml\Profile;

use Magento\Framework\Filesystem;

class SearchTaxonomy extends \Xtento\ProductExport\Controller\Adminhtml\Profile
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @var Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * SearchTaxonomy constructor.
     *
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
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param Filesystem\Directory\ReadFactory $readFactory
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
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
    ) {
        $this->moduleReader = $moduleReader;
        $this->readFactory = $readFactory;
        parent::__construct($context, $moduleHelper, $cronHelper, $profileCollectionFactory, $registry, $escaper, $scopeConfig, $dateFilter, $entityHelper, $profileFactory);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);

        $searchTerm = $this->getRequest()->getPost('term');
        $taxonomySource = $this->getRequest()->getParam('source');
        $isGoogle = preg_match('/^google/', $taxonomySource);

        // Get file for source
        $definedTaxonomies = $this->moduleHelper->getTaxonomies();
        if (!array_key_exists($taxonomySource, $definedTaxonomies)) {
            return $resultPage->setData(['error' => __('Undefined taxonomy.')]);
        }

        $taxonomyFolder = $this->moduleReader->getModuleDir(
                \Magento\Framework\Module\Dir::MODULE_ETC_DIR,
                'Xtento_ProductExport'
            ) . DIRECTORY_SEPARATOR . 'data';

        $directoryRead = $this->readFactory->create($taxonomyFolder);
        $taxonomyData = $directoryRead->readFile($definedTaxonomies[$taxonomySource]);

        if (empty($searchTerm) || empty($taxonomyData)) {
            return $resultPage->setData(['error' => __('Search term empty or taxonomy file not found/loadable.')]);
        }

        $results = [];
        $taxonomyContent = explode("\n", $taxonomyData);
        foreach ($taxonomyContent as $taxonomyRow) {
            if (substr($taxonomyRow, 0, 1) === '#') {
                continue;
            }
            if (stripos($taxonomyRow, $searchTerm) !== false) {
                if ($isGoogle) {
                    $parsedTaxonomy = explode(" - ", $taxonomyRow);
                    $taxonomyId = array_shift($parsedTaxonomy);
                    $results[] = ['id' => $taxonomyId, 'label' => implode(" - ", $parsedTaxonomy)];
                } else {
                    $results[] = ['id' => $taxonomyRow, 'label' => $taxonomyRow];
                }
            }
        }

        return $resultPage->setData($results);
    }
}
