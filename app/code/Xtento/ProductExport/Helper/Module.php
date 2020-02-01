<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-27T13:37:38+00:00
 * File:          app/code/Xtento/ProductExport/Helper/Module.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Helper;

class Module extends \Xtento\XtCore\Helper\AbstractModule
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Module constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Xtento\XtCore\Helper\Server $serverHelper
     * @param \Xtento\XtCore\Helper\Utils $utilsHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Xtento\XtCore\Helper\Server $serverHelper,
        \Xtento\XtCore\Helper\Utils $utilsHelper,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context, $registry, $serverHelper, $utilsHelper);
        $this->resource = $resource;
    }

    protected $edition = 'EE';
    protected $module = 'Xtento_ProductExport';
    protected $extId = 'MTWOXtento_ProductExport990990';
    protected $configPath = 'productexport/general/';

    // Module specific functionality below
    public function getDebugEnabled()
    {
        return $this->scopeConfig->isSetFlag($this->configPath . 'debug');
    }

    public function isDebugEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            $this->configPath . 'debug'
        ) && ($debug_email = $this->scopeConfig->getValue($this->configPath . 'debug_email')) && !empty($debug_email);
    }

    public function getDebugEmail()
    {
        return $this->scopeConfig->getValue($this->configPath . 'debug_email');
    }

    public function isModuleProperlyInstalled()
    {
        return true; // Not required, Magento 2 does the job of handling upgrades better than Magento 1
        // Check if DB table(s) have been created.
        return ($this->resource->getConnection('core_read')->showTableStatus(
                $this->resource->getTableName('xtento_productexport_profile')
            ) !== false);
    }

    public function getExportBkpDir()
    {
        return rtrim($this->serverHelper->getBaseDir()->getAbsolutePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "productexport_bkp" . DIRECTORY_SEPARATOR;
    }

    /**
     * Get taxonomy files, for category mappings
     *
     * @return array
     */
    public function getTaxonomies()
    {
        return [
            'google_en_US' => 'google/taxonomy-with-ids.en-US.txt',
            'google_en_AU' => 'google/taxonomy-with-ids.en-AU.txt',
            'google_en_GB' => 'google/taxonomy-with-ids.en-GB.txt',
            'google_cs_CZ' => 'google/taxonomy-with-ids.cs-CZ.txt',
            'google_da_DK' => 'google/taxonomy-with-ids.da-DK.txt',
            'google_de_DE' => 'google/taxonomy-with-ids.de-DE.txt',
            'google_de_CH' => 'google/taxonomy-with-ids.de-CH.txt',
            'google_es_ES' => 'google/taxonomy-with-ids.es-ES.txt',
            'google_fr_CH' => 'google/taxonomy-with-ids.fr-CH.txt',
            'google_fr_FR' => 'google/taxonomy-with-ids.fr-FR.txt',
            'google_it_CH' => 'google/taxonomy-with-ids.it-CH.txt',
            'google_it_IT' => 'google/taxonomy-with-ids.it-IT.txt',
            'google_ja_JP' => 'google/taxonomy-with-ids.ja-JP.txt',
            'google_nl_NL' => 'google/taxonomy-with-ids.nl-NL.txt',
            'google_no_NO' => 'google/taxonomy-with-ids.no-NO.txt',
            'google_pl_PL' => 'google/taxonomy-with-ids.pl-PL.txt',
            'google_pt_BR' => 'google/taxonomy-with-ids.pt-BR.txt',
            'google_ru_RU' => 'google/taxonomy-with-ids.ru-RU.txt',
            'google_sv_SE' => 'google/taxonomy-with-ids.sv-SE.txt',
            'google_tr_TR' => 'google/taxonomy-with-ids.tr-TR.txt',
            'bing_en_US' => 'bing/bing-category-taxonomy-us.txt',
            'bing_de_DE' => 'bing/bing-category-taxonomy-de.txt',
            'bing_fr_FR' => 'bing/bing-category-taxonomy-fr.txt'
        ];
    }
}
