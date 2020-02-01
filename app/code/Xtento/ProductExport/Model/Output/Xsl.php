<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-28T10:55:27+00:00
 * File:          app/code/Xtento/ProductExport/Model/Output/Xsl.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Model\Output;

use Magento\Framework\Exception\LocalizedException;

class Xsl extends AbstractOutput
{
    protected $searchCharacters;
    protected $replaceCharacters;

    /**
     * @var XmlFactory
     */
    protected $outputXmlFactory;

    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    protected $exportSettings;

    /**
     * Xsl constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Xtento\XtCore\Helper\Date $dateHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Xtento\ProductExport\Model\ProfileFactory $profileFactory
     * @param \Xtento\ProductExport\Model\ResourceModel\History\CollectionFactory $historyCollectionFactory
     * @param \Xtento\ProductExport\Model\ResourceModel\Log\CollectionFactory $logCollectionFactory
     * @param XmlFactory $outputXmlFactory
     * @param \Magento\Framework\Config\DataInterface $exportSettings
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
        XmlFactory $outputXmlFactory,
        \Magento\Framework\Config\DataInterface $exportSettings,
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
        $this->outputXmlFactory = $outputXmlFactory;
        $this->exportSettings = $exportSettings;
    }


    public function convertData($exportArray)
    {
        if (!class_exists('\XSLTProcessor')) {
            throw new LocalizedException(__('The XSLTProcessor class could not be found. This means your PHP installation is missing XSL features. You cannot export output formats using XSL Templates without the PHP XSL extension. Please get in touch with your hoster or server administrator to add XSL to your PHP configuration.'));
        }
        // Some libxml settings, constants
        $libxmlConstants = null;
        if (defined('LIBXML_PARSEHUGE')) {
            $libxmlConstants = LIBXML_PARSEHUGE;
        }
        $useInternalXmlErrors = libxml_use_internal_errors(true);
        #if (function_exists('libxml_disable_entity_loader')) {
            #$loadXmlEntities = libxml_disable_entity_loader(true);
        #}
        libxml_clear_errors();

        $outputArray = [];
        // Should the ampersand character etc. be encoded?
        $escapeSpecialChars = false;
        if (preg_match('/method="(xml|html)"/', $this->getProfile()->getXslTemplate())) {
            $escapeSpecialChars = true;
        }
        // Get fields which should not be escaped
        $disableEscapingFields = [];
        if (preg_match_all('/disable-escaping-fields="(.*)"/', $this->getProfile()->getXslTemplate(), $disableEscapingFields)) {
            if (isset($disableEscapingFields[1]) && isset($disableEscapingFields[1][0])) {
                $disableEscapingFields = explode(",", $disableEscapingFields[1][0]);
            }
        }
        // Convert to XML first
        $convertedData = $this->outputXmlFactory->create()->setProfile($this->getProfile())->setEscapeSpecialChars($escapeSpecialChars)->setDisableEscapingFields($disableEscapingFields)->convertData($exportArray);
        // Get "first" file from returned data.
        $convertedXml = array_pop($convertedData);
        // If there are problems with bad/destroyed encodings in the DB:
        // $convertedXml = utf8_encode(utf8_decode($convertedXml));
        $xmlDoc = new \DOMDocument;
        if (!$xmlDoc->loadXML($convertedXml, $libxmlConstants)) {
            $this->throwXmlException(__("Could not load internally processed XML. Bad data maybe?"));
        }
        // Load different file templates
        $outputFormatMarkup = $this->getProfile()->getXslTemplate();
        if (empty($outputFormatMarkup)) {
            throw new LocalizedException(__('No XSL Template has been set up for this export profile. Please open the export profile and set up your XSL Template in the "Output Format" tab.'));
        }
        try {
            $outputFormatXml = new \SimpleXMLElement($outputFormatMarkup, null, strpos($outputFormatMarkup, '<') === false);
        } catch (\Exception $e) {
            $this->throwXmlException(__("Please repair the XSL Template of this profile. You need to have a valid XSL Template in order to export orders. Could not load XSL Template:"));
        }
        $outputFormats = $outputFormatXml->xpath('//files/file');
        if (empty($outputFormats)) {
            throw new LocalizedException(__('No <files><file></file></files> markup found in XSL Template. Please repair your XSL Template.'));
        }
        // Loop through each <file> node
        foreach ($outputFormats as $outputFormat) {
            $fileAttributes = $outputFormat->attributes();
            $filename = trim($this->replaceFilenameVariables($this->getSimpleXmlElementAttribute($fileAttributes->filename), $exportArray));
            $blacklistedFileExtensions = ['.php', '.phtml', '.htaccess'];
            foreach ($blacklistedFileExtensions as $blacklistedFileExtension) {
                while (preg_match('/\\' . $blacklistedFileExtension . '$/', $filename) === 1) {
                    $filename = preg_replace('/\\' . $blacklistedFileExtension . '$/', '.txt', $filename);
                }
            }

            $charsetEncoding = $this->getSimpleXmlElementAttribute($fileAttributes->encoding);
            $charsetLocale = $this->getSimpleXmlElementAttribute($fileAttributes->locale);
            $searchCharacters = $this->getSimpleXmlElementAttribute($fileAttributes->search);
            $replaceCharacters = $this->getSimpleXmlElementAttribute($fileAttributes->replace);
            $quoteHandling = $this->getSimpleXmlElementAttribute($fileAttributes->quotes);
            $addUtf8Bom = ($this->getSimpleXmlElementAttribute($fileAttributes->addUtf8Bom) == 1) ? true : false;

            $xslTemplate = current($outputFormat->xpath('*'))->asXML();
            $xslTemplate = $this->preparseXslTemplate($xslTemplate);

            // XSL Template
            $xslTemplateObj = new \XSLTProcessor();
            $allowedPhpFunctions = array_merge(explode(",", $this->exportSettings->get('allowed_php_functions')), explode(",", $this->exportSettings->get('custom_allowed_php_functions')));
            $xslTemplateObj->registerPHPFunctions($allowedPhpFunctions);
            // Add some parameters accessible as $variables in the XSL Template (example: <xsl:value-of select="$exportid"/>)
            $this->addVariablesToXSLT($xslTemplateObj, $exportArray, $xslTemplate);
            // Import stylesheet
            /* Alternative DOMDocument version for versions that don't like SimpleXMLElements in importStylesheet */
            /*
            $domDocument = new DOMDocument();
            $domDocument->loadXML($xslTemplate);
            $xslTemplateObj->importStylesheet($domDocument);
            */
            $xslTemplateObj->importStylesheet(new \SimpleXMLElement($xslTemplate));
            if (libxml_get_last_error() !== FALSE) {
                $this->throwXmlException(__("Please repair the XSL Template of this profile. There was a problem processing the XSL Template:"));
            }

            $adjustedXml = false;
            // Replace certain characters
            if (!empty($searchCharacters)) {
                $this->searchCharacters = str_split(str_replace(['quote'], ['"'], $searchCharacters));
                if (in_array('"', $this->searchCharacters)) {
                    $replacePosition = array_search('"', $this->searchCharacters);
                    if ($replacePosition !== false) {
                        $this->searchCharacters[$replacePosition] = '&quot;';
                    }
                }
                $this->replaceCharacters = str_split($replaceCharacters);
                $adjustedXml = preg_replace_callback('/<(.*)>(.*)<\/(.*)>/um', [$this, 'replaceCharacters'], $convertedXml);
            }
            // Handle quotes in field data
            if (!empty($quoteHandling)) {
                $ampSign = '&';
                if ($escapeSpecialChars) {
                    $ampSign = '&amp;';
                }
                if ($quoteHandling == 'double') {
                    $quoteReplaceData = $ampSign . 'quot;' . $ampSign . 'quot;';
                } else if ($quoteHandling == 'remove') {
                    $quoteReplaceData = '';
                } else {
                    $quoteReplaceData = $quoteHandling;
                }
                if ($adjustedXml !== false) {
                    $adjustedXml = str_replace($ampSign . "quot;", $quoteReplaceData, $adjustedXml);
                } else {
                    $adjustedXml = str_replace($ampSign . "quot;", $quoteReplaceData, $convertedXml);
                }
            }
            if ($adjustedXml !== false) {
                $xmlDoc->loadXML($adjustedXml, $libxmlConstants);
            }

            $outputBeforeEncoding = '';
            try {
                $outputBeforeEncoding = $xslTemplateObj->transformToXML($xmlDoc);
            } catch (\Exception $e) {}
            $output = $this->changeEncoding($outputBeforeEncoding, $charsetEncoding, $charsetLocale);
            if (!$output && !empty($outputBeforeEncoding)) {
                $this->throwXmlException(__("Please repair the XSL Template of this profile, check the encoding tag, or make sure output has been generated by this template. No output has been generated."));
            }
            if ($addUtf8Bom) {
                $utf8Bom = pack('H*', 'EFBBBF');
                $output = $utf8Bom . $output;
            }
            $outputArray[$filename] = $output;
        }
        // Reset libxml settings
        libxml_use_internal_errors($useInternalXmlErrors);
        #if (function_exists('libxml_disable_entity_loader')) {
            #libxml_disable_entity_loader($loadXmlEntities);
        #}
        // Return generated files
        return $outputArray;
    }

    protected function getSimpleXmlElementAttribute($data)
    {
        if ($data === null) {
            return "";
        }
        $current = false;
        try {
            $current = current($data);
        } catch (\Exception $e) {}
        if ($current === false) {
            $stringData = (string)$data;
            if (isset($data[0])) {
                return $data[0];
            } else if ($stringData !== '') {
                return $stringData;
            }
        }
        return $current;
    }

    protected function replaceCharacters($matches)
    {
        return "<$matches[1]>" . str_replace($this->searchCharacters, $this->replaceCharacters, $matches[2]) . "</$matches[3]>";
    }

    protected function addVariablesToXSLT(\XSLTProcessor $xslTemplateObj, $exportArray, $xslTemplateXml)
    {
        if ($this->isRequiredInXslTemplate('$collectioncount', $xslTemplateXml)) {
            // Collection count
            $xslTemplateObj->setParameter('', 'collectioncount', $this->getVariableValue('collection_count', $exportArray));
        }
        // Export ID
        if ($this->isRequiredInXslTemplate('$exportid', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'exportid', $this->getVariableValue('export_id', $exportArray));
        }
        // Date information
        if ($this->isRequiredInXslTemplate('$dateFromTimestamp', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'dateFromTimestamp', $this->getVariableValue('date_from_timestamp', $exportArray));
        }
        if ($this->isRequiredInXslTemplate('$dateToTimestamp', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'dateToTimestamp', $this->getVariableValue('date_to_timestamp', $exportArray));
        }
        // GUID
        if ($this->isRequiredInXslTemplate('$guid', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'guid', $this->getVariableValue('guid', $exportArray));
        }
        // Current timestamp
        if ($this->isRequiredInXslTemplate('$timestamp', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'timestamp', $this->localeDate->scopeTimeStamp());
        }
        // How often was this object exported before by this profile?
        if ($this->isRequiredInXslTemplate('$exportCountForObject', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'exportCountForObject', $this->getVariableValue('export_count_for_object', $exportArray));
        }
        // How many objects have been exported today by this profile?
        if ($this->isRequiredInXslTemplate('$dailyExportCounter', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'dailyExportCounter', $this->getVariableValue('daily_export_counter', $exportArray));
        }
        // How many objects have been exported by this profile? Basically an incrementing counter for each export
        if ($this->isRequiredInXslTemplate('$profileExportCounter', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'profileExportCounter', $this->getVariableValue('profile_export_counter', $exportArray));
        }
        // Root category ID for store that is exported
        if ($this->isRequiredInXslTemplate('$rootCategoryId', $xslTemplateXml)) {
            $xslTemplateObj->setParameter('', 'rootCategoryId', $this->getVariableValue('root_category_id', $exportArray));
        }
        return $this;
    }

    /*
     * Check if the variable is used in the XSL Template and only if yes return true
     */
    protected function isRequiredInXslTemplate($variable, $xslTemplateXml)
    {
        if (strpos($xslTemplateXml, $variable) === FALSE) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * Many old XSL Templates are still using products/product. Replace with objects/object on the fly.
     */
    protected function preparseXslTemplate($xslTemplate)
    {
        return str_replace(
            '<xsl:for-each select="products/product">',
            '<xsl:for-each select="objects/object">',
            $xslTemplate
        );
    }
}