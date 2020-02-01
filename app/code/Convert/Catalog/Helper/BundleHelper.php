<?php

namespace Convert\Catalog\Helper;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 *
 * @package Convert\Catalog\Helper
 */
class BundleHelper extends AbstractHelper
{
    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProduct;
    /**
     * @var \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle
     */
    protected $bundleBlock;
    protected $productFactory;
    protected $layoutFactory;
    protected $coreRegistry;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle $bundleBlock,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->bundleBlock = $bundleBlock;
        $this->catalogProduct = $catalogProduct;
        $this->layoutFactory = $layoutFactory;
        $this->productFactory = $productFactory;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function checkCanAddToCart($product)
    {
        $stripSelection = $product->getConfigureMode() ? true : false;
        $options = $this->bundleBlock->decorateArray($this->getOptions($product,$stripSelection));
        $cannotAdd = false;
        foreach ($options as $option) {
            $isDefault = false;
            if(count($option->getSelections()) == 1 ){
                $isDefault = true;
            }else{
                foreach ($option->getSelections() as $selection) {
                    if($selection->getIsDefault() == true){
                        $isDefault = true;
                        break;
                    }
                }
            }
            if(!$isDefault)
                $cannotAdd = true;
        }
        return !$cannotAdd;
    }

    public function getOptions($product, $stripSelection = false)
    {
            /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $typeInstance->setStoreFilter($product->getStoreId(), $product);

            $optionCollection = $typeInstance->getOptionsCollection($product);

            $selectionCollection = $typeInstance->getSelectionsCollection(
                $typeInstance->getOptionsIds($product),
                $product
            );
            \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor::class)->addPriceData($selectionCollection);
            $selectionCollection->addTierPriceData();

            $options = $optionCollection->appendSelections(
                $selectionCollection,
                $stripSelection,
                $this->catalogProduct->getSkipSaleableCheck()
            );

        return $options;
    }

    public function getBundleOptionsHtml($product)
    {
        $_product = $this->productFactory->create()->load($product->getId());

        $this->coreRegistry->unregister('current_product');/*this used for default file of observer*/
        $this->coreRegistry->register('current_product',$_product);/*this used for default file of observer*/
        $layout = $this->layoutFactory->create();
        $blockOption = $layout->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle', 'type_bundle_options')->setProduct($_product)->setTemplate('Magento_Bundle::catalog/product/view/type/bundle/options.phtml');
        $price_renderer_block = $layout
            ->createBlock(
                "Magento\Framework\Pricing\Render", "product.price.render.default", [
                    'data' => [
                        'price_render_handle' => 'catalog_product_prices',
                        'use_link_for_as_low_as' => 'true'
                    ]
                ]
            )
            ->setData('area', 'frontend');

        $blockOption->setChild('product.price.render.default', $price_renderer_block);


        $block_links2 = $layout->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Multi', 'multi')->setProduct($_product);
        $blockOption->setChild('multi', $block_links2);

        $block_links3 = $layout->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Radio', 'radio')->setProduct($_product);
        $blockOption->setChild('radio', $block_links3);


        $block_links4 = $layout->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Select', 'select')->setProduct($_product);
        $blockOption->setChild('select', $block_links4);


        $block_links5 = $layout->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox', 'checkbox')->setProduct($_product);
        $blockOption->setChild('checkbox', $block_links5);


        return $blockOption->toHtml();

    }
}