<?php
/**
 * Created by pp
 * @project magento202
 */

namespace Unirgy\RapidFlowPro\Helper\ProtectedCode;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

class Context
{
    public $logger;

    public $scopeConfig;

    public $modelProductImage;

    /**
     * Context constructor.
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\Image $productImage
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\Image $productImage
    ) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->modelProductImage = $productImage;
    }
}
