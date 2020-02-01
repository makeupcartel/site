<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Observer\System;

use Amasty\ElasticSearch\Model\Config;
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ConfigChanged implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $reinitableConfig;

    public function __construct(
        Config $config,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        $this->config = $config;
        $this->reinitableConfig = $reinitableConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $name = $observer->getEvent()->getName();
        $catalogValue = $this->getCatalogValue();
        $moduleValue = $this->getElasticModuleValue();
        if ($catalogValue != $moduleValue) {
            switch ($name) {
                case 'admin_system_config_changed_section_catalog':
                    $this->saveElasticValue($catalogValue);
                    break;
                case 'admin_system_config_changed_section_amasty_elastic':
                    $this->saveCatalogValue($moduleValue);
                    break;
            }
        }
    }

    /**
     * @param $value
     */
    private function saveElasticValue($value)
    {
        $this->saveConfig(Config::CONFIG_ENGINE_PATH, $value);
    }

    /**
     * @param $value
     */
    private function saveCatalogValue($value)
    {
        $this->saveConfig(EngineInterface::CONFIG_ENGINE_PATH, $value);
    }

    /**
     * @param string $path
     * @param string $value
     * @return $this
     */
    private function saveConfig($path, $value)
    {
        $this->configWriter->save($path, $value);
        $this->reinitableConfig->reinit();
        return $this;
    }

    /**
     * @return string
     */
    private function getCatalogValue()
    {
        return $this->config->getGeneralConfig(EngineInterface::CONFIG_ENGINE_PATH);
    }

    /**
     * @return string
     */
    private function getElasticModuleValue()
    {
        return $this->config->getEngine();
    }
}
