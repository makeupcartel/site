<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\Cart\Block;

use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\ObjectManagerInterface;

class Product extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Amasty\Cart\Helper\Data
	 */
	protected $_helper;

	/**
	 * @var \Magento\Catalog\Block\Product\ImageBuilder
	 */
	protected $imageBuilder;

	/**
	 * @var FormKey
	 */
	protected $formKey;
	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $_registry;

	protected $_objectManager;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
		\Magento\Framework\Registry $registry,
		ObjectManagerInterface $objectManager,
		FormKey $formKey,
		array $data = [],
		\Amasty\Cart\Helper\Data $helper
	)
	{
		parent::__construct($context, $data);

		$this->_helper = $helper;
		$this->_objectManager = $objectManager;
		$this->formKey = $formKey;
		$this->_scopeConfig = $context->getScopeConfig();
		$this->imageBuilder = $imageBuilder;
		$this->_registry = $registry;
	}

	public function getHelper()
	{
		return $this->_helper;
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
		$simpleProduct = $this->_registry->registry('amasty_cart_simple_product');
		if ($simpleProduct) {
			$product = $simpleProduct;
		}

		return $this->imageBuilder->setProduct($product)
			->setImageId($imageId)
			->setAttributes($attributes)
			->create();
	}

	public function getFormKey()
	{
		return $this->formKey->getFormKey();
	}
	
}
