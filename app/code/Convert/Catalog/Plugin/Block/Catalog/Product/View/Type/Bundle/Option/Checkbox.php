<?php

namespace Convert\Catalog\Plugin\Block\Catalog\Product\View\Type\Bundle\Option;

/**
 * Class Checkbox
 *
 * @package Convert\Catalog\Plugin\Block\Catalog\Product\View\Type\Bundle\Option
 */
class Checkbox extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox
{
    /**
     * @param \Magento\Catalog\Model\Product $selection
     * @param bool $includeContainer
     * @return string
     */
    public function getSelectionQtyTitlePrice($selection, $includeContainer = true)
    {
        $this->setFormatProduct($selection);
        $priceTitle = '<span class="product-name">'
            . $selection->getSelectionQty() * 1
            . ' x '
            . $this->escapeHtml($selection->getName())
            . '</span>';
        return $priceTitle;
    }
}