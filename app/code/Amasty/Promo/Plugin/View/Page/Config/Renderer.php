<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\View\Page\Config;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Page\Config\Renderer as MagentoRenderer;

class Renderer
{
    const CACHE_KEY = 'amasty_promo_should_load_css_file';

    protected $filesToCheck = ['css/styles-l.css', 'css/styles-m.css'];

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $config;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(
        \Magento\Framework\View\Page\Config $config,
        CacheInterface $cache
    ) {
        $this->config = $config;
        $this->cache = $cache;
    }

    /**
     * Add our css file if less functionality is missing
     *
     * @param MagentoRenderer $subject
     * @param array $resultGroups
     * @return array
     */
    public function beforeRenderAssets(
        MagentoRenderer $subject,
        $resultGroups = []
    ) {
        $shouldLoad = $this->cache->load(self::CACHE_KEY);
        if ($shouldLoad === false) {
            $shouldLoad = $this->isShouldLoadCss();
            $this->cache->save($shouldLoad, self::CACHE_KEY);
        }

        if ($shouldLoad) {
            $this->config->addPageAsset('Amasty_Promo::css/source/mkcss/ampromo.css');
        }

        return [$resultGroups];
    }

    /**
     * @return int
     */
    private function isShouldLoadCss()
    {
        $collection = $this->config->getAssetCollection();
        $found = 0;
        foreach ($collection->getAll() as $item) {
            /** @var File $item */
            if ($item instanceof File
                && in_array($item->getFilePath(), $this->filesToCheck)
            ) {
                $found++;
            }
        }

        return (int)($found < count($this->filesToCheck));
    }
}
