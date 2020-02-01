<?php

namespace Convert\SkinQuiz\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Result extends Template
{
	protected $priceCurrency;

	protected $imageHelperFactory;

	protected $listBlock;

	public function __construct(
		Template\Context $context,
		PriceCurrencyInterface $priceCurrency,
		\Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
		\Magento\Catalog\Block\Product\ListProduct $listBlock,
		array $data = []
	)
	{
		$this->priceCurrency = $priceCurrency;
		$this->imageHelperFactory = $imageHelperFactory;
		$this->listBlock = $listBlock;
		parent::__construct($context, $data);
	}

	public function getFormatedPrice($amount)
    {
        return $this->priceCurrency->convertAndFormat($amount);
	}
	
	public function getProductThumbnailUrl($product)
	{
		return $this->imageHelperFactory->create()->init($product, 'wishlist_thumbnail')->getUrl();
	}

	public function getAddToCartUrl($product)
	{
		return $this->listBlock->getAddToCartUrl($product);
	}

	public function checkCaseProduct($skinTypeResults, $SkinQuizExclude)
	{
		$amountResult = 0;
		foreach ($skinTypeResults as $item) 
		{
				if (in_array($item, $SkinQuizExclude)) {
					$amountResult++;
				}
		}

		if ($amountResult == count($skinTypeResults)) {
			return 0;
		}else{
			return 1;
		}
	}
}
