<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Plugin\Block\Catalog\Product\ProductList\Item\AddTo;

class Wishlist
{
    /**
     * @var \Amasty\Groupcat\Model\ProductRuleProvider
     */
    private $ruleProvider;

    /**
     * Wishlist constructor.
     * @param \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider
     */
    public function __construct(
        \Amasty\Groupcat\Model\ProductRuleProvider $ruleProvider
    ) {
        $this->ruleProvider = $ruleProvider;
    }

    /**
     * @param \Magento\Wishlist\Block\Catalog\Product\ProductList\Item\AddTo\Wishlist $subject
     * @param \Closure $proceed
     * @return string
     */
    public function aroundToHtml($subject, $proceed)
    {
        if ($this->ruleProvider->getProductIsHideWishlist($subject->getProduct())) {
            return '';
        }
        return $proceed();
    }
}
