<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-07-16T13:39:10+00:00
 * File:          app/code/Xtento/ProductExport/Ui/Plugin/Component/MassActionPlugin.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Ui\Plugin\Component;

use Magento\Ui\Component\MassAction;

/**
 * Class MassActionPlugin
 * @package Xtento\ProductExport\Ui\Plugin\Component
 */
class MassActionPlugin
{
    /**
     * @var \Xtento\ProductExport\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $adminhtmlData = null;

    /**
     * @var \Xtento\ProductExport\Model\System\Config\Source\Export\ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var \Xtento\ProductExport\Helper\Entity
     */
    protected $entityHelper;

    /**
     * MassActionPlugin constructor.
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Xtento\ProductExport\Model\System\Config\Source\Export\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Helper\Entity $entityHelper
     */
    public function __construct(
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Xtento\ProductExport\Model\System\Config\Source\Export\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Helper\Entity $entityHelper
    ) {
        $this->moduleHelper = $moduleHelper;
        $this->request = $request;
        $this->scopeConfig = $config;
        $this->registry = $registry;
        $this->authorization = $authorization;
        $this->adminhtmlData = $adminhtmlData;
        $this->profileFactory = $profileFactory;
        $this->entityHelper = $entityHelper;
    }

    /**
     * Add massactions to the Products > Catalog grid.
     * Why not via XML? Because then you cannot select the actions which should be shown from
     * the Magento admin, this is required so admins can adjust the actions via the configuration.
     *
     * @param MassAction $subject
     * @param string $interceptedOutput
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // @codingStandardsIgnoreStart
    public function afterPrepare($subject, $interceptedOutput)
    {
        // @codingStandardsIgnoreEnd
        $gridId = $subject->getContext()->getNamespace();
        if (!in_array($gridId, $this->getGridIdentifiers())) {
            return;
        }
        if (!$this->moduleHelper->isModuleEnabled()) {
            return;
        }
        if ($this->registry->registry('xtDisabled') !== false) {
            return;
        }
        if (!$this->authorization->isAllowed('Xtento_ProductExport::manual')) {
            return;
        }
        $dataProvider = $subject->getContext()->getDataProvider()->getName(); // E.g.: product_listing_data_source
        preg_match('/(.*)\_listing_data/', $dataProvider, $dataProviderMatches);
        if (isset($dataProviderMatches[1]) && !empty($dataProviderMatches[1])) {
            $entity = $dataProviderMatches[1];
        } else {
            return;
        }

        $config = $subject->getData('config');

        if (!isset($config['component']) || strstr($config['component'], 'tree') === false) {
            // Temporary until added to core to support multi-level selects
            $config['component'] = 'Magento_Ui/js/grid/tree-massactions';
        }

        if (!isset($config['actions'])) {
            $config['actions'] = [];
        }
        $config['actions'] = $this->addExportAction($subject, $config['actions'], $entity);

        $subject->setData('config', $config);
    }

    protected function addExportAction($subject, $configActions, $entity)
    {
        $subActions = [];
        $exportProfiles = $this->profileFactory->create()->toOptionArray(false, $entity);
        foreach ($exportProfiles as $exportProfile) {
            $subActions[] = [
                'type' => 'profile_' . $exportProfile['value'],
                'label' => __('Profile: %1', $exportProfile['label']),
                'url' => $this->adminhtmlData->getUrl(
                    'xtento_productexport/manual/gridPost',
                    [
                        'type' => $entity,
                        'profile_id' => $exportProfile['value'],
                        'namespace' => $subject->getContext()->getNamespace()
                    ]
                )
            ];
        }

        $configActions[] = [
            'type' => 'xtento_' . $entity . '_export',
            'label' => __('Export %1', $this->entityHelper->getPluralEntityName($entity)),
            'actions' => $subActions
        ];

        return $configActions;
    }

    /*
     * Get controller names where the module is supposed to modify the block
     */
    protected function getGridIdentifiers($entity = false)
    {
        $gridIdentifiers = [];
        if (!$entity || $entity == \Xtento\ProductExport\Model\Export::ENTITY_PRODUCT) {
            array_push($gridIdentifiers, 'product_listing');
        }
        return $gridIdentifiers;
    }
}
