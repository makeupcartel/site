<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2018-07-11T12:35:25+00:00
 * File:          app/code/Xtento/ProductExport/Model/Profile.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model;

class Profile extends \Magento\Rule\Model\AbstractModel
{
    /**
     * @var Export\Condition\CombineFactory
     */
    protected $combineFactory;

    /**
     * @var Export\Condition\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var \Xtento\ProductExport\Helper\Module
     */
    protected $moduleHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var DestinationFactory
     */
    protected $destinationFactory;

    /**
     * @var ResourceModel\History\CollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var \Xtento\XtCore\Helper\Cron
     */
    protected $cronHelper;

    /**
     * Profile constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Export\Condition\CombineFactory $combineFactory
     * @param Export\Condition\ActionFactory $actionFactory
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param \Xtento\XtCore\Helper\Cron $cronHelper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param DestinationFactory $destinationFactory
     * @param ResourceModel\History\CollectionFactory $historyCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Xtento\ProductExport\Model\Export\Condition\CombineFactory $combineFactory,
        \Xtento\ProductExport\Model\Export\Condition\ActionFactory $actionFactory,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        \Xtento\XtCore\Helper\Cron $cronHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Xtento\ProductExport\Model\DestinationFactory $destinationFactory,
        \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;
        $this->actionFactory = $actionFactory;
        $this->moduleHelper = $moduleHelper;
        $this->cronHelper = $cronHelper;
        $this->request = $request;
        $this->destinationFactory = $destinationFactory;
        $this->historyCollectionFactory = $historyCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Xtento\ProductExport\Model\ResourceModel\Profile');
        $this->_collectionName = 'Xtento\ProductExport\Model\ResourceModel\Profile\Collection';
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        $this->_registry->register('productexport_profile', $this, true);
        return $this->combineFactory->create();
    }

    /**
     * @return \Magento\Rule\Model\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->actionFactory->create();
    }

    public function getDestinations()
    {
        $destinationIds = array_filter(explode("&", $this->getData('destination_ids')));
        $destinations = [];
        foreach ($destinationIds as $destinationId) {
            if (!is_numeric($destinationId)) {
                continue;
            }
            $destination = $this->destinationFactory->create()->load($destinationId);
            if ($destination->getId()) {
                $destinations[] = $destination;
            }
        }
        if ($this->getSaveFilesLocalCopy()) {
            // Add "faked" local destination to save copies of all exports in ./var/export_bkp/
            $destination = $this->destinationFactory->create();
            $destination->setBackupDestination(true);
            $destination->setName("Backup Local Destination");
            $destination->setType(Destination::TYPE_LOCAL);
            $destination->setPath($this->moduleHelper->getExportBkpDir());
            $destinations[] = $destination;
        }
        // Return destinations
        return $destinations;
    }

    public function beforeSave()
    {
        // Only call the "rule" model parents _beforeSave function if the profile is modified in the backend, as otherwise the "conditions" ("export filters") could be lost
        if ($this->request->getModuleName() == 'xtento_productexport' && $this->request->getControllerName() == 'profile') {
            parent::beforeSave();
        } else {
            if (!$this->getId()) {
                $this->isObjectNew(true);
            }
        }
        return $this;
    }

    public function afterSave()
    {
        parent::afterSave();
        if ($this->request->getModuleName() == 'xtento_productexport' && ($this->request->getControllerName() == 'profile' || $this->request->getControllerName() == 'tools')) {
            $this->updateCronjobs();
        }
        if ($this->_registry->registry('xtento_productexport_update_cronjobs_after_profile_save') !== null) {
            // Can be registered by third party developers, so after they call ->save() on a profile, it will update the profiles cronjobs, added in version 2.2.6
            $this->updateCronjobs();
        }
        return $this;
    }

    public function beforeDelete()
    {
        // Remove existing cronjobs
        $this->cronHelper->removeCronjobsLike('productexport_profile_' . $this->getId() . '_%');

        return parent::beforeDelete();
    }

    /**
     * Update database via cron helper
     */
    protected function updateCronjobs()
    {
        // Remove existing cronjobs
        $this->cronHelper->removeCronjobsLike('productexport_profile_' . $this->getId() . '_%');

        if (!$this->getEnabled()) {
            return $this; // Profile not enabled
        }
        if (!$this->getCronjobEnabled()) {
            return $this; // Cronjob not enabled
        }

        $cronRunModel = 'Xtento\ProductExport\Cron\Export::execute';
        if ($this->getCronjobFrequency() == \Xtento\ProductExport\Model\System\Config\Source\Cron\Frequency::CRON_CUSTOM
            || ($this->getCronjobFrequency() == '' && $this->getCronjobCustomFrequency() !== '')
        ) {
            // Custom cron expression
            $cronFrequencies = $this->getCronjobCustomFrequency();
            if (empty($cronFrequencies)) {
                return $this;
            }
            $cronFrequencies = array_unique(explode(";", $cronFrequencies));
            $cronCounter = 0;
            foreach ($cronFrequencies as $cronFrequency) {
                $cronFrequency = trim($cronFrequency);
                if (empty($cronFrequency)) {
                    continue;
                }
                $cronCounter++;
                $cronIdentifier = 'productexport_profile_' . $this->getId() . '_cron_' . $cronCounter;
                $this->cronHelper->addCronjob(
                    $cronIdentifier,
                    $cronFrequency,
                    $cronRunModel
                );
            }
        } else {
            // No custom cron expression
            $cronFrequency = $this->getCronjobFrequency();
            if (empty($cronFrequency)) {
                return $this;
            }
            $cronIdentifier = 'productexport_profile_' . $this->getId() . '_cron';
            $this->cronHelper->addCronjob(
                $cronIdentifier,
                $cronFrequency,
                $cronRunModel
            );
        }

        return $this;
    }

    public function saveLastExecutionNow()
    {
        $write = $this->getResource()->getConnection();
        $write->update(
            $this->getResource()->getMainTable(),
            ['last_execution' => (new \DateTime)->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)],
            ["`{$this->getResource()->getIdFieldName()}` = {$this->getId()}"]
        );
    }
}