<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Plugin\Block\Catalog\Product\ProductList\Item\AddTo;

class Compare
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
     * @param \Magento\Catalog\Block\Product\ProductList\Item\AddTo\Compare $subject
     * @param \Closure $proceed
     * @return string
     */
    public function aroundToHtml($subject, $proceed)
    {
        if ($this->ruleProvider->getProductIsHideCompare($subject->getProduct())) {
            return '';
        }
        return $proceed();
    }
}
