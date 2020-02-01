<?php

namespace Convert\Quickorder\Block\Product;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Render;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\ActionInterface;

class ProductList extends \Magento\Framework\View\Element\Template
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = Toolbar::class;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var ListProduct
     */
    protected $listProductBlock;

    /**
     * @var \Magento\Catalog\Block\Product\ReviewRendererInterface
     */
    protected $reviewRenderer;

    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $_compareProduct;

    /**
     * @var Toolbar
     */
    protected $_toolbar;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_helperQuickorder;

    /**
     * ProductList constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Block\Product\Context $gridContext
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param ListProduct $listProductBlock
     * @param Toolbar $toolbar
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Convert\Quickorder\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Block\Product\Context $gridContext,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Block\Product\ListProduct $listProductBlock,
        Toolbar $toolbar,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Convert\Quickorder\Helper\Data $helper,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->listProductBlock = $listProductBlock;
        $this->reviewRenderer = $gridContext->getReviewRenderer();
        $this->_compareProduct = $gridContext->getCompareProduct();
        $this->_toolbar = $toolbar;
        $this->_storeManager = $storeManager;
        $this->_helperQuickorder = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->pageConfig->getTitle()->set(__('Quick Order'));
        if (count($this->getLoadedProductCollection())) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Catalog\Block\Product\ProductList\Toolbar',
                'quick.order.pager'
            )->setCollection($this->getLoadedProductCollection());
            $this->setChild('toolbar', $pager);
            $this->getLoadedProductCollection()->load();
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function isRedirectToCartEnabled()
    {
        return $this->_scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getProductDetailsHtml(\Magento\Catalog\Model\Product $product)
    {
        $renderer = $this->getDetailsRenderer($product->getTypeId());
        if ($renderer) {
            $renderer->setProduct($product);
            return $renderer->toHtml();
        }
        return '';
    }

    /**
     * @param null $type
     * @return |null
     */
    public function getDetailsRenderer($type = null)
    {
        if ($type === null) {
            $type = 'default';
        }
        $rendererList = $this->getDetailsRendererList();
        if ($rendererList) {
            return $rendererList->getRenderer($type, 'default');
        }
        return null;
    }

    /**
     * @return bool|\Magento\Framework\View\Element\AbstractBlock|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getDetailsRendererList()
    {
        return $this->getDetailsRendererListName() ? $this->getLayout()->getBlock(
            $this->getDetailsRendererListName()
        ) : $this->getChildBlock(
            'quickorder.toprenderers'
        );
    }

    /**
     * @param Product $product
     * @param null $priceType
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductPricetoHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null
    ) {
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product
            );
        }
        return $price;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLoadedProductCollection()
    {
        $categoryIds = $this->_helperQuickorder->getCategoryIdIsQuickorder();
        $collection = [];
        if (count($categoryIds)) {
            $collection = $this->_productCollectionFactory->create();
            $collection
                ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
                ->addAttributeToFilter('status',
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('type_id', ['in' => ['configurable', 'simple']]);
            if ($storeId = $this->getRequest()->getParam('store')) {
                $collection->addStoreFilter($storeId);
            }
            $columns = [
                'category_id',
                'position',

            ];
            $collection->joinTable(array('category' => 'catalog_category_product'), 'product_id = entity_id',$columns);
            $collection->getSelect()->where('category_id IN('.implode(',', $categoryIds).')');
            $field = $this->getRequest()->getParam('product_list_order');
            if ($field != 'position') {
                $collection->setOrder($field, 'ASC');
            }
            if(!$field || $field == 'position'){
                $collection->getSelect()->order('position','ASC');
            }
            $list = $this->getLayout()->getBlock('quick.order.products.list');
            $list->setCollection($collection);
        }
        return $collection;
    }

    /**
     * @param Product $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductPrice(Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => Render::ZONE_ITEM_LIST,
                    'list_category_page' => true
                ]
            );
        }

        return $price;
    }

    /**
     * Specifies that price rendering should be done for the list of products
     * i.e. rendering happens in the scope of product list, but not single product
     *
     * @return Render
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default')
            ->setData('is_product_list', true);
    }

    /**
     * Retrieve list toolbar HTML
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    public function getAllStore()
    {
        $stores = $this->_storeManager->getStores();
        $options = [];
        foreach ($stores as $item) {
            if ($item->getStoreId() == 1) {
                $options[] = ['id' => "", 'name' => 'All'];
            } else {
                $a = ucfirst($item->getName());
                $a = str_replace("Cosmetics", "", $a);
                $options[] = ['id' => $item->getStoreId(), 'name' => $a];
            }
        }
        return $options;
    }
}
