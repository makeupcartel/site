<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Post;

use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Source\Config\Related\BlockLayout;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Api\Data\PostInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render as PricingRender;
use Magento\Framework\Data\Helper\PostHelper;

/**
 * Class RelatedProduct
 * @method PostInterface getPost()
 * @package Aheadworks\Blog\Block\Post
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RelatedProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * Path to template file in theme
     * @var string
     */
    protected $_template = 'Aheadworks_Blog::post/related_product.phtml';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ImageBuilder
     */
    private $imageBuilder;

    /**
     * @var CartHelper
     */
    private $cartHelper;

    /**
     * @var PostHelper
     */
    private $postHelper;

    /**
     * @param Context $context
     * @param Config $config
     * @param ProductRepositoryInterface $productRepository
     * @param PostHelper $postHelper
     * @param ImageBuilder $imageBuilder
     * @param CartHelper $cartHelper
     * @param PostHelper $postHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        ProductRepositoryInterface $productRepository,
        ImageBuilder $imageBuilder,
        CartHelper $cartHelper,
        PostHelper $postHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->productRepository = $productRepository;
        $this->imageBuilder = $imageBuilder;
        $this->cartHelper = $cartHelper;
        $this->postHelper = $postHelper;
    }

    /**
     * Retrieve PostHelper object
     *
     * @return PostHelper
     */
    public function getPostDataHelper()
    {
        return $this->postHelper;
    }

    /**
     * Retrieve product model by product id
     *
     * @param int $productId
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProductById($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * Retrieve add to cart url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Retrieve product url
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }
        return '#';
    }

    /**
     * Retrieve product price value
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => PricingRender::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }

    /**
     * Get mage init script
     *
     * @return string
     */
    public function getDataMageInit()
    {
        switch ($this->getBlockLayout()) {
            case BlockLayout::SLIDER_VALUE:
                $dataMageInit = '{"awBlogRelatedSlider": {}}';
                break;
            case BlockLayout::SINGLE_ROW_VALUE:
                $dataMageInit = '{"awBlogRelatedGrid": {}}';
                break;
            case BlockLayout::MULTIPLE_ROWS_VALUE:
                $dataMageInit = '{}';
                break;
            default:
                $dataMageInit = '{}';
        }
        return $dataMageInit;
    }

    /**
     * Get related product ids for post
     *
     * @return int[]
     */
    public function getRelatedProductIds()
    {
        return array_slice($this->getPost()->getRelatedProductIds(), 0, $this->config->getRelatedProductsLimit());
    }

    /**
     * Get block layout
     *
     * @return string
     */
    public function getBlockLayout()
    {
        return $this->config->getRelatedBlockLayout();
    }

    /**
     * Check whether Add to cart button should be displayed
     *
     * @return bool
     */
    public function isDisplayAddToCart()
    {
        return $this->config->isRelatedDisplayAddToCart();
    }

    /**
     * Check Product has URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    private function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }
}
