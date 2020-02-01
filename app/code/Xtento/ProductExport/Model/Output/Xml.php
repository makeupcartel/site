<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-27T12:30:21+00:00
 * File:          app/code/Xtento/ProductExport/Model/Output/Xml.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Output;

use Magento\Framework\Exception\LocalizedException;

class Xml extends AbstractOutput
{
    /**
     * @var Xml\WriterFactory
     */
    protected $xmlWriterFactory;

    /**
     * Xml constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
     * @param \Xtento\ProductExport\Model\ResourceModel\Log\CollectionFactory $logCollectionFactory
     * @param Xml\WriterFactory $xmlWriterFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Xtento\XtCore\Helper\Date $dateHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Xtento\ProductExport\Model\ProfileFactory $profileFactory,
        \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory,
        \Xtento\ProductExport\Model\ResourceModel\Log\CollectionFactory $logCollectionFactory,
        Xml\WriterFactory $xmlWriterFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $localeDate,
            $dateHelper,
            $profileFactory,
            $historyCollectionFactory,
            $logCollectionFactory,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
        $this->xmlWriterFactory = $xmlWriterFactory;
    }

    public function convertData($exportArray)
    {
        if (!class_exists('\XMLWriter')) {
            throw new LocalizedException(__('The XMLWriter class could not be found. This means your PHP installation is missing XMLWriter features. You cannot export XML/XSL types without XMLWriter. Please get in touch with your hoster or server administrator to add XMLWriter features.'));
        }
        // Some libxml settings
        $useInternalXmlErrors = libxml_use_internal_errors(true);
        #if (function_exists('libxml_disable_entity_loader')) {
            #$loadXmlEntities = libxml_disable_entity_loader(true);
        #}
        libxml_clear_errors();

        #ini_set('xdebug.var_display_max_depth', 5);
        #Zend_Debug::dump($exportArray); die();
        $profile = $this->getProfile();
        if ($profile->getOutputType() == 'xml') {
            $escapeSpecialChars = true;
            $disableEscapingFields = [];
        } else {
            $escapeSpecialChars = $this->getEscapeSpecialChars();
            $disableEscapingFields = $this->getDisableEscapingFields();
        }
        $xmlWriter = $this->xmlWriterFactory->create();
        $xmlWriter->setEscapeSpecialChars($escapeSpecialChars);
        $xmlWriter->setDisableEscapingFields($disableEscapingFields);
        $xmlWriter->fromArray($exportArray);
        $outputXml = $xmlWriter->getDocument();
        if (libxml_get_last_error() !== FALSE) {
            $this->throwXmlException(__("Something is wrong with the internally processed XML markup. Please contact XTENTO."));
        }
        // Force UTF-8:
        // $outputXml = iconv(mb_detect_encoding($outputXml, mb_detect_order(), true), "UTF-8", $outputXml);
        // Handle output if the profiles output format is directly the master XML format
        if ($profile->getOutputType() == 'xml') {
            // Output all fields into a XML file
            $filename = $this->replaceFilenameVariables($profile->getFilename(), $exportArray);
            $charsetEncoding = $profile->getEncoding();
            $outputXml = $this->changeEncoding($outputXml, $charsetEncoding);
            $outputData[$filename] = $outputXml;
        } else {
            // We use the output for the XSL Template
            $outputData[] = $outputXml;
        }

        // Reset libxml settings
        libxml_use_internal_errors($useInternalXmlErrors);
        #if (function_exists('libxml_disable_entity_loader')) {
            #libxml_disable_entity_loader($loadXmlEntities);
        #}
        // Return data
        return $outputData;
    }
}