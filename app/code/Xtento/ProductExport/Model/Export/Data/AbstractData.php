<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-28T11:47:13+00:00
 * File:          app/code/Xtento/ProductExport/Model/Export/Data/AbstractData.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Export\Data;

abstract class AbstractData extends \Magento\Framework\Model\AbstractModel implements DataInterface
{
    protected $cache;
    protected $writeArray;
    protected $fieldsToFetch = false;
    protected $fieldsNotFound = [];
    protected $fieldsFound = [];

    /**
     * @var \Xtento\XtCore\Helper\Date
     */
    protected $dateHelper;

    /**
     * @var \Xtento\XtCore\Helper\Utils
     */
    protected $utilsHelper;

    /**
     * AbstractData constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->dateHelper = $dateHelper;
        $this->utilsHelper = $utilsHelper;
    }

    protected function _construct()
    {
        $this->initConfiguration($this->getConfiguration());
    }

    protected function initConfiguration($configuration)
    {
        foreach ($configuration as $key => $value) {
            $this->setData($key, $value);
        }
        return $this;
    }

    /*
     * Checks XSL template if field X is used there at all and thus if it should be fetched to avoid unnecessary DB queries and memory usage.
     */
    protected function initFieldsToFetch()
    {
        $this->fieldsToFetch = [];
        if ($this->getProfile()->getOutputType() == 'csv' || $this->getProfile()->getOutputType() == 'xml') {
            // Fetch all fields
            return $this;
        }
        $xslTemplate = $this->getProfile()->getXslTemplate();
        $fileExists = false;
        try {
            $fileExists = file_exists($xslTemplate);
        } catch (\Exception $e) {}
        if (!strstr($xslTemplate, '<file') && $fileExists) {
            // XSL Template is loaded from file, fetch fields from there
            $xslTemplate = '';
            try {
                $xslTemplate = file_get_contents($xslTemplate);
            } catch (\Exception $e) {}
            if (empty($xslTemplate)) {
                return $this; // Fetch all fields
            }
        }
        preg_match_all("/(select=\"([^\"]+)\"|test=\"([^\"]+)\")/", $xslTemplate, $fieldMatches);
        if (isset($fieldMatches[1])) {
            foreach ($fieldMatches[1] as $fieldMatch) {
                if (!in_array($fieldMatch, $this->fieldsToFetch)) {
                    array_push($this->fieldsToFetch, $fieldMatch);
                }
            }
        }
        // Fields which must be fetched always
        array_push($this->fieldsToFetch, 'entity_id');
        array_push($this->fieldsToFetch, 'created_at');
        #var_dump($fieldMatches[1], $this->_fieldsToFetch); die();
        return $this;
    }

    /*
     * Check if field should be fetched from the DB
     */
    protected function fieldLoadingRequired($field)
    {
        #return true;
        if ($this->fieldsToFetch === false) {
            $this->initFieldsToFetch();
        }
        if (empty($this->fieldsToFetch) || $this->getShowEmptyFields()) {
            return true;
        }
        $fieldHash = sha1($field);
        if (isset($this->fieldsNotFound[$fieldHash])) {
            return false;
        }
        if (isset($this->fieldsFound[$fieldHash])) {
            return true;
        }
        if (!in_array($field, $this->fieldsToFetch)) {
            foreach ($this->fieldsToFetch as $fieldToFetch) {
                if (stristr($fieldToFetch, $field)) {
                    $this->fieldsFound[$fieldHash] = true;
                    return true;
                }
            }
            $this->fieldsNotFound[$fieldHash] = true;
            return false;
        }
        $this->fieldsFound[$fieldHash] = true;
        return true;
    }

    /*
     * Is "depends_module" an installed module/extension?
     */
    public function confirmDependency()
    {
        if (!$this->getDependsModule()) {
            return true;
        }
        return $this->utilsHelper->isExtensionInstalled($this->getDependsModule());
    }

    protected function writeValue($field, $value, $customWriteArray = false)
    {
        if ($this->fieldLoadingRequired($field) && !is_object($value)) {
            if (($field !== null && !is_array($value) && $value !== null && $value !== '') || ($this->getShowEmptyFields() && !is_array($value))) {
                if ($this->getProfile()->getExportReplaceNlBr() != 0) {
                    if ($this->getProfile()->getExportReplaceNlBr() == 3) {
                        $value = str_replace(["\r\n", "\r", "\n"], "", $value);
                    } else if ($this->getProfile()->getExportReplaceNlBr() == 2) {
                        $value = str_replace(["\r\n", "\r", "\n"], " ", $value);
                    } else if ($this->getProfile()->getExportReplaceNlBr() == 1) {
                        $value = str_replace(["\r\n", "\r", "\n"], "<br />", $value);
                    }
                }
                if ($this->getProfile() && $this->getProfile()->getExportStripTags() && !is_array($value) && !is_object($value)) {
                    $value = strip_tags($value);
                }
                if (!$customWriteArray) {
                    $this->writeArray[$field] = $value;
                } else {
                    $this->writeArray[$customWriteArray][$field] = $value;
                }
            } else if (is_array($value)) {
                foreach ($value as $k => $v) {
                    if ($this->getProfile() && $this->getProfile()->getExportStripTags() && !is_array($v) && !is_object($v)) {
                        $v = strip_tags($v);
                    }
                    if (!is_array($v)) $this->writeValue($k, $v, $field);
                }
            }
        }
    }

    /*
     * Get store ID of export profile
     */
    protected function getStoreId()
    {
        $storeId = 0;
        if ($this->getProfile() && $this->getProfile()->getStoreId()) {
            $storeId = $this->getProfile()->getStoreId();
        }
        return $storeId;
    }
}