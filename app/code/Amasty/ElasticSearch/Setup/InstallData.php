<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ElasticSearch
 */


namespace Amasty\ElasticSearch\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    const STOP_WORD_DIRECTORY = '/../etc/stopwords/';
    const STOP_WORD_NAME = 'stopwords_';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     * @var \Amasty\ElasticSearch\Api\StopWordRepositoryInterface
     */
    private $stopWordRepository;

    /**
     * @var \Amasty\ElasticSearch\Model\Debug
     */
    private $debug;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $resolver;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Amasty\ElasticSearch\Api\StopWordRepositoryInterface $stopWordRepository,
        \Amasty\ElasticSearch\Model\Debug $debug,
        \Magento\Framework\Locale\Resolver $resolver
    ) {
        $this->storeManager = $storeManager;
        $this->ioFile = $ioFile;
        $this->stopWordRepository = $stopWordRepository;
        $this->debug = $debug;
        $this->resolver = $resolver;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->generateDefaultStopWords();
        $this->addQuerySettingsDefaultData($setup);
    }

    private function generateDefaultStopWords()
    {
        $path = __DIR__. self::STOP_WORD_DIRECTORY;
        $currentStore = $this->storeManager->getStore();

        /** @var \Magento\Store\Api\Data\StoreInterface $store */
        foreach ($this->storeManager->getStores(false) as $store) {
            try {
                $this->resolver->emulate($store->getId());
                $locale = $this->resolver->getLocale();
                $file = $path . self::STOP_WORD_NAME  . $locale . '.csv';
                if ($locale && $this->ioFile->fileExists($file)) {
                    $count = $this->stopWordRepository->importStopWords($file, $store->getId());
                    $this->debug->debug(
                        __('%1 StopWords were imported for store %2', $count, $store->getName())->render()
                    );
                } else {
                    $this->debug->debug(__('There are no file for import: %1', $file)->render());
                }
            } catch (\Exception $exception) {
                $this->debug->debug($exception->getMessage());
            }
        }

        $this->resolver->emulate($currentStore->getId());
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function addQuerySettingsDefaultData(ModuleDataSetupInterface $setup)
    {
        $value  = '{"sku":{"wildcard":"0","spelling":"0","combining":"0"},'
            . '"name":{"wildcard":"1","spelling":"0","combining":"0"},'
            . '"short_description":{"wildcard":"0","spelling":"1","combining":"1"},'
            . '"description":{"wildcard":"0","spelling":"1","combining":"1"}}';
        $data = [
            'scope' => 'default',
            'scope_id' => 0,
            'path' => 'amasty_elastic/catalog/query_settings',
            'value' => $value
        ];

        $setup->getConnection()
            ->insertOnDuplicate($setup->getTable('core_config_data'), $data, ['value']);
        $setup->endSetup();
    }
}
