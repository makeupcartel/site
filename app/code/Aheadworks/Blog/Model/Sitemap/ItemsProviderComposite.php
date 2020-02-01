<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Sitemap;

use Aheadworks\Blog\Model\Sitemap\ItemsProvider\ProviderInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ItemsProviderComposite
 * @package Aheadworks\Blog\Model\Sitemap
 */
class ItemsProviderComposite
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProviderInterface[]
     */
    private $providers;

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param LoggerInterface $logger
     * @param array $providers
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        LoggerInterface $logger,
        array $providers = []
    ) {
        $this->productMetadata = $productMetadata;
        $this->logger = $logger;
        $this->providers = $providers;
    }

    /**
     * Retrieve sitemap items
     *
     * @param int $storeId
     * @return array
     */
    public function getItems($storeId)
    {
        $items = [];
        $method = $this->resolveMethod();
        foreach ($this->providers as $provider) {
            if ($provider instanceof ProviderInterface) {
                $items = array_merge($items, $provider->{$method}($storeId));
            } elseif (is_object($provider)) {
                $this->logger->warning(__('%1 doesn\'t implement %2', get_class($provider), ProviderInterface::class));
            } else {
                $this->logger->warning(__('Given provider doesn\'t implement %1', ProviderInterface::class));
            }
        }

        return $items;
    }

    /**
     * Resolve provider method
     *
     * @return string
     */
    private function resolveMethod()
    {
        return version_compare($this->productMetadata->getVersion(), '2.3', '>=')
            ? 'getItems23x'
            : 'getItems';
    }
}
