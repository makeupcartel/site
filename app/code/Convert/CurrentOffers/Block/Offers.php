<?php

namespace Convert\CurrentOffers\Block;

/**
 * Class Offers
 *
 * @package Convert\CurrentOffers\Block
 */
class Offers extends \Magento\Framework\View\Element\Template
{

    /**
     * @return \Magento\Framework\Phrase
     */
	public function getBlogInfo()
	{
		return __('Offers module');
	}
}
