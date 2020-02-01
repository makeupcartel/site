<?php

namespace Unirgy\RapidFlowPro\Model\ResourceModel;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Unirgy\RapidFlow\Model\Config as ModelConfig;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Fixed;
use Unirgy\RapidFlow\Model\ResourceModel\Catalog\Product\AbstractProduct;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\ObjectManager;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;

class ProductExtra2
    extends ProductExtra
{
}