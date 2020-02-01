<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Plugin\Config\Model;

class Config
{
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var \Amasty\ElasticSearch\Model\Config
     */
    private $elasticConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    public function __construct(
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Amasty\ElasticSearch\Model\Config $elasticConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->elasticConfig = $elasticConfig;
        $this->config = $config;
    }

    /**
     * @param \Magento\Config\Model\Config $subject
     * @return array
     */
    public function beforeSave(\Magento\Config\Model\Config $subject) {
        $groups = $subject->getGroups();
        if ($this->isSpecialCharsOptionChanged($groups) || $this->isShowOutOfStockOptionChanged($groups)) {
            $this->indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)->invalidate();
        }
        
        return [];
    }

    /**
     * @param array $groups
     * @return bool
     */
    private function isSpecialCharsOptionChanged(array $groups)
    {
        $result = false;
        if (isset($groups['catalog']['fields']['long_tail']['value'])) {
            $specialCharValue = $groups['catalog']['fields']['long_tail']['value'];
            $specialCharValueOrigin = $this->elasticConfig->getModuleConfig('catalog/long_tail');
            $result = $specialCharValue !== $specialCharValueOrigin;
        }
        return $result;
    }

    /**
     * @param array $groups
     * @return bool
     */
    private function isShowOutOfStockOptionChanged(array $groups)
    {
        $result = false;
        $outOfStockValue = false;
        if (isset($groups['options']['fields']['show_out_of_stock']['value'])) {
            $outOfStockValue = $groups['options']['fields']['show_out_of_stock']['value'];
        } elseif (isset($groups['options']['fields']['show_out_of_stock']['inherit'])) {
            $outOfStockValue = '0';
        }

        if ($outOfStockValue !== false) {
            $outOfStockValueOrigin = $this->config->getValue('cataloginventory/options/show_out_of_stock');
            $result = $outOfStockValue !== $outOfStockValueOrigin;
        }
        return $result;
    }
}
