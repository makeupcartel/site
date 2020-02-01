<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */
namespace Amasty\Cart\Plugin\Product;

class ProductsList
{
    /**
     * @var \Amasty\Cart\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var bool
     */
    private $initialized = false;

    public function __construct(
        \Amasty\Cart\Helper\Data $helper,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->helper = $helper;
        $this->layoutFactory = $layoutFactory;
    }

    public function afterToHtml(
        $subject,
        $result
    ) {
        $enable = $this->helper->getModuleConfig('general/enable');

        if ($enable && !$this->initialized) {
            $layout = $this->layoutFactory->create();
            $block = $layout->createBlock(
                'Amasty\Cart\Block\Config',
                'amasty.cart.config',
                [ 'data' => [] ]
            );

            $html = $block->setPageType('category')->toHtml();
            $result .= $html;
            $this->initialized = true;
        }

        return  $result;
    }
}
