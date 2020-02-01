<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Helper;

use Amasty\Xsearch\Block\Search\Category;
use Amasty\Xsearch\Block\Search\Page;
use Amasty\Xsearch\Block\Search\Popular;
use Amasty\Xsearch\Block\Search\Product;
use Amasty\Xsearch\Block\Search\Recent;
use Amasty\Xsearch\Block\Search\Landing;
use Amasty\Xsearch\Block\Search\Brand;
use Amasty\Xsearch\Block\Search\Blog;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match as QueryMatchBuilder;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const MODULE_NAME = 'amasty_xsearch/';
    const XML_PATH_TEMPLATE_CATEGORY_POSITION = 'category/position';
    const XML_PATH_TEMPLATE_PRODUCT_POSITION = 'product/position';
    const XML_PATH_TEMPLATE_PAGE_POSITION = 'page/position';
    const XML_PATH_TEMPLATE_POPULAR_SEARCHES_POSITION = 'popular_searches/position';
    const XML_PATH_TEMPLATE_RECENT_SEARCHES_POSITION = 'recent_searches/position';
    const XML_PATH_TEMPLATE_LANDING_POSITION = 'landing_page/position';
    const XML_PATH_TEMPLATE_BRAND_POSITION = 'brand/position';
    const XML_PATH_TEMPLATE_BLOG_POSITION = 'blog/position';

    const XML_PATH_TEMPLATE_CATEGORY_ENABLED = 'category/enabled';
    const XML_PATH_TEMPLATE_PRODUCT_ENABLED = 'product/enabled';
    const XML_PATH_TEMPLATE_PAGE_ENABLED = 'page/enabled';
    const XML_PATH_TEMPLATE_POPULAR_SEARCHES_ENABLED = 'popular_searches/enabled';
    const XML_PATH_TEMPLATE_RECENT_SEARCHES_ENABLED = 'recent_searches/enabled';
    const XML_PATH_TEMPLATE_LANDING_ENABLED = 'landing_page/enabled';
    const XML_PATH_TEMPLATE_BRAND_ENABLED = 'brand/enabled';
    const XML_PATH_TEMPLATE_BLOG_ENABLED = 'blog/enabled';

    const XML_PATH_IS_SINGLE_PRODUCT_REDIRECT = 'product/redirect_single_product';
    const XML_PATH_IS_SEO_URL_ENABLED = 'general/enable_seo_url';
    const XML_PATH_SEO_KEY = 'general/seo_key';
    const XML_PATH_POPUP_INDEX = 'general/enable_popup_index';

    /**
     * @var \Magento\Catalog\Model\Config
     */
    private $configAttribute;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    private $collection;

    /**
     * @var \Magento\Search\Helper\Data
     */
    private $searchHelper;

    public function __construct(
        \Magento\Catalog\Model\Config $configAttribute,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection,
        \Magento\Search\Helper\Data $searchHelper,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->configAttribute = $configAttribute;
        $this->collection = $collection;
        $this->searchHelper = $searchHelper;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue(self::MODULE_NAME . $path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param $block
     * @return bool
     */
    public function isIndexEnable($block = null)
    {
        $isIndexEnable = $this->getModuleConfig(self::XML_PATH_POPUP_INDEX);

        return $isIndexEnable || ($block !== null && ($block instanceof \Amasty\Xsearch\Block\Search\Category));
    }

    /**
     * @param $text
     * @param $query
     * @return string
     */
    public function highlight($text, $query)
    {
        if ($query) {
            preg_match_all('~\w+~u', $query, $matches);

            if ($matches && isset($matches[0])) {
                $re = '/(' . implode('|', $matches[0]) . ')/iu';
                $text = preg_replace($re, '<span class="amsearch-highlight ">$0</span>', $text);
            }
        }

        return $text;
    }

    /**
     * @param $position
     * @param $block
     * @param $result
     */
    protected function _pushItem($position, $block, &$result)
    {
        $positions = explode('/', $position);
        $type = isset($positions[0]) ? $positions[0] : false;
        $position = $this->getModuleConfig($position) * 10; // x10 - fix sorting issue
        while (isset($result[$position])) {
            $position++;
        }
        $currentHtml = $block->toHtml();

        $result[$position] = [
            'type' =>  $type,
            'html' => $currentHtml
        ];
    }

    /**
     * @param \Magento\Framework\View\Layout $layout
     * @return array
     */
    public function getBlocksHtml(\Magento\Framework\View\Layout $layout)
    {
        $result = [];

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_PRODUCT_ENABLED)) {
            /** @var Product $productsBlock */
            $productsBlock = $layout->createBlock(Product::class, 'amasty.xsearch.product');
            $results = $productsBlock->getResults();

            $this->_pushItem(
                self::XML_PATH_TEMPLATE_PRODUCT_POSITION,
                $productsBlock,
                $result
            );

            if ($this->isSingleProductRedirect() && count($results) == 1) {
                $item = array_pop($results);
                $result['redirect_url'] = $item['url'];
            }
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_CATEGORY_ENABLED)) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_CATEGORY_POSITION,
                $layout->createBlock(Category::class, 'amasty.xsearch.category'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_PAGE_ENABLED)) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_PAGE_POSITION,
                $layout->createBlock(Page::class, 'amasty.xsearch.page'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_POPULAR_SEARCHES_ENABLED)) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_POPULAR_SEARCHES_POSITION,
                $layout->createBlock(Popular::class, 'amasty.xsearch.search.popular'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_RECENT_SEARCHES_ENABLED)) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_RECENT_SEARCHES_POSITION,
                $layout->createBlock(Recent::class, 'amasty.xsearch.search.recent'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_LANDING_ENABLED)
            && $this->_moduleManager->isEnabled('Amasty_Xlanding')
        ) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_LANDING_POSITION,
                $layout->createBlock(Landing::class, 'amasty.xsearch.landing.page'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_BRAND_ENABLED)
            && $this->_moduleManager->isEnabled('Amasty_ShopbyBrand')
        ) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_BRAND_POSITION,
                $layout->createBlock(Brand::class, 'amasty.xsearch.brand.page'),
                $result
            );
        }

        if ($this->getModuleConfig(self::XML_PATH_TEMPLATE_BLOG_ENABLED)
            && $this->_moduleManager->isEnabled('Amasty_Blog')
        ) {
            $this->_pushItem(
                self::XML_PATH_TEMPLATE_BLOG_POSITION,
                $layout->createBlock(Blog::class, 'amasty.xsearch.blog.page'),
                $result
            );
        }

        ksort($result);

        return $result;
    }

    /**
     * @param string $requiredData
     * @return array
     */
    public function getProductAttributes($requiredData = '')
    {
        if ($requiredData == 'is_searchable') {
            $attributeNames = [];
            foreach ($this->collection->addIsSearchableFilter()->getItems() as $attribute) {
                $attributeNames[] = $attribute->getAttributeCode();
            }

            return $attributeNames;
        } else {
            return $this->collection->getItems();
        }
    }

    public function isSingleProductRedirect()
    {
        return $this->getModuleConfig(self::XML_PATH_IS_SINGLE_PRODUCT_REDIRECT);
    }

    /**
     * @param string $query
     * @return string
     */
    public function getResultUrl($query = null)
    {
        return $this->searchHelper->getResultUrl($query);
    }

    /**
     * @return bool
     */
    public function isNoIndexNoFollowEnabled()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isSeoUrlsEnabled()
    {
        return (bool)$this->getModuleConfig(self::XML_PATH_IS_SEO_URL_ENABLED);
    }

    /**
     * @return string
     */
    public function getSeoKey()
    {
        return (string)trim($this->getModuleConfig(self::XML_PATH_SEO_KEY));
    }

    /**
     * @param $query
     * @return $query
     */
    public function setStrippedQueryText($query)
    {
        if (strpos($this->scopeConfig->getValue(EngineProvider::CONFIG_ENGINE_PATH), 'mysql') !== false) {
            $replaceSymbols = str_split(QueryMatchBuilder::SPECIAL_CHARACTERS, 1);
            $query->setQueryText(str_replace($replaceSymbols, ' ', $query->getQueryText()));
        }

        return $query;
    }
}
