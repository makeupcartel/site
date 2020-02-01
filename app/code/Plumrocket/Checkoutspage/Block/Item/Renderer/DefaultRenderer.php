<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Checkoutspage v2.x.x
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Block\Item\Renderer;

class DefaultRenderer extends \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer
{

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils            $string
     * @param \Magento\Catalog\Block\Product\ImageBuilder      $imageBuilder
     * @param \Magento\Catalog\Model\Product\OptionFactory     $productOptionFactory
     * @param \Magento\Catalog\Model\ProductFactory            $productFactory
     * @param \Magento\Catalog\Helper\Product                  $productHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        array $data = []
    ) {
    	$this->_productHelper = $productHelper;
        $this->_productFactory = $productFactory;
        $this->imageBuilder = $imageBuilder;
        parent::__construct($context,$string,$productOptionFactory, $data);
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
            ->setAttributes($attributes)
            ->setImageId($imageId)
            ->create();
    }

    /**
     * Retrieve child product
     * @return \Magento\Catalog\Model\Product
     */
    protected function _getChildProduct()
    {

        if ($this->getProduct()->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $childs = $this->getItem()->getChildrenItems();
            if (count($childs)) {
                return $childs[0]->getProduct();
            }
        }
        return $this->getProduct();
    }

    /**
     * Get product for thunbnail image
     * @return [type] [description]
     */
    public function getProductForThumbnail()
    {

        if ($this->getProduct()->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return $this->getItem()->getProduct();
        }

        if ($this->_scopeConfig->getValue(
            \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable::CONFIG_THUMBNAIL_SOURCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) == \Magento\Catalog\Model\Config\Source\Product\Thumbnail::OPTION_USE_PARENT_IMAGE ||
            !($this->_getChildProduct()->getThumbnail() && $this->_getChildProduct()->getThumbnail() != 'no_selection')
        ) {
            $product = $this->getProduct();
        } else {
            $product = $this->_getChildProduct();
        }
        return $product;
    }

    /**
     * Retrieve product
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->getItem()->getProduct();
    }


}