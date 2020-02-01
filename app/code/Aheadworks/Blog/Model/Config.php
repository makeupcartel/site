<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Blog config
 */
class Config
{
    /**
     * Configuration path to blog is enabled flag
     */
    const XML_PATH_ENABLED = 'aw_blog/general/enabled';

    /**
     * Configuration path to blog is navigation menu link enabled flag
     */
    const XML_PATH_NAVIGATION_MENU_LINK_ENABLED = 'aw_blog/general/navigation_menu_link_enabled';

    /**
     * Configuration path to route to blog
     */
    const XML_PATH_ROUTE_TO_BLOG = 'aw_blog/general/route_to_blog';

    /**
     * Configuration path to blog title
     */
    const XML_PATH_BLOG_TITLE = 'aw_blog/general/blog_title';

    /**
     * Configuration path to route to authors page
     */
    const XML_PATH_ROUTE_TO_AUTHORS = 'aw_blog/general/route_to_authors';

    /**
     * Configuration path to number of posts per page
     */
    const XML_PATH_POSTS_PER_PAGE = 'aw_blog/general/posts_per_page';

    /**
     * Configuration path to quantity of related posts
     */
    const XML_PATH_QTY_OF_RELATED_POSTS = 'aw_blog/general/related_posts_qty';

    /**
     * Configuration path to positions of sharing buttons to display
     */
    const XML_PATH_DISPLAY_SHARING_AT = 'aw_blog/general/display_sharing_buttons_at';

    /**
     * Configuration path to comments are enabled flag
     */
    const XML_PATH_COMMENTS_ENABLED = 'aw_blog/general/comments_enabled';

    /**
     * Configuration path to number of recent posts to display
     */
    const XML_PATH_RECENT_POSTS = 'aw_blog/sidebar/recent_posts';

    /**
     * Configuration path to number of most popular tags to display
     */
    const XML_PATH_POPULAR_TAGS = 'aw_blog/sidebar/popular_tags';

    /**
     * Configuration path to "highlight popular tags" flag
     */
    const XML_PATH_HIGHLIGHT_TAGS = 'aw_blog/sidebar/highlight_popular_tags';

    /**
     * Configuration path to sidebar CMS block ID
     */
    const XML_PATH_SIDEBAR_CMS_BLOCK = 'aw_blog/sidebar/cms_block';

    /**
     * Configuration path to sidebar Category Listing Enabled
     */
    const XML_PATH_SIDEBAR_CATEGORY_LISTING_ENABLED = 'aw_blog/sidebar/category_listing_enabled';

    /**
     * Configuration path to sidebar Category Listing Enabled
     */
    const XML_PATH_SIDEBAR_CATEGORY_LISTING_LIMIT = 'aw_blog/sidebar/category_listing_limit';

    /**
     * Configuration path to blog meta description
     */
    const XML_PATH_META_DESCRIPTION = 'aw_blog/seo/meta_description';

    /**
     * Configuration path to url type
     */
    const XML_PATH_SEO_URL_TYPE = 'aw_blog/seo/url_type';

    /**
     *  Configuration path to blog facebook application ID
     */
    const XML_PATH_FACEBOOK_APP_ID = 'aw_blog/general/facebook_app_id';

    /**
     *  Configuration path to twitter site
     */
    const XML_PATH_META_TWITTER_SITE = 'aw_blog/general/twitter_site';

    /**
     * Configuration path to blog change frequency
     */
    const XML_PATH_SITEMAP_CHANGEFREQ = 'sitemap/aw_blog/changefreq';

    /**
     * Configuration path to blog priority
     */
    const XML_PATH_SITEMAP_PRIORITY = 'sitemap/aw_blog/priority';

    /**
     * Configuration path to display blog posts tab on product page
     */
    const XML_PATH_RELATED_DISPLAY_POSTS_ON_PRODUCT_PAGE = 'aw_blog/related_products/display_posts_on_product_page';

    /**
     * Configuration path to display related products block on post page
     */
    const XML_PATH_RELATED_BLOCK_POSITION = 'aw_blog/related_products/block_position';

    /**
     * Configuration path to related products block layout
     */
    const XML_PATH_RELATED_BLOCK_LAYOUT = 'aw_blog/related_products/block_layout';

    /**
     * Configuration path to max products to display
     */
    const XML_PATH_RELATED_PRODUCTS_LIMIT = 'aw_blog/related_products/products_limit';

    /**
     * Configuration path to display "Add to Cart" button
     */
    const XML_PATH_RELATED_DISPLAY_ADD_TO_CART = 'aw_blog/related_products/display_add_to_cart';

    /**
     *  Configuration path to store name
     */
    const XML_PATH_STORE_INFORMATION_NAME = 'general/store_information/name';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get website id
     * Workaround solution to fix Magento bug https://github.com/magento/magento2/issues/7943
     *
     * @param null $storeId
     * @return int
     */
    private function getWebsiteId($storeId = null)
    {
        $store = $this->storeManager->getStore($storeId);
        if ($store) {
            return $store->getWebsiteId();
        } else {
            return null;
        }
    }

    /**
     * Check if blog is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isBlogEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_WEBSITE,
            $this->getWebsiteId($storeId)
        );
    }

    /**
     * Check is navigation menu link enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isMenuLinkEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_NAVIGATION_MENU_LINK_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get route to blog
     *
     * @param int|null $storeId
     * @return string
     */
    public function getRouteToBlog($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ROUTE_TO_BLOG,
            ScopeInterface::SCOPE_WEBSITE,
            $this->getWebsiteId($storeId)
        );
    }

    /**
     * Get route to authors
     *
     * @param int|null $storeId
     * @return string
     */
    public function getRouteToAuthors($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ROUTE_TO_AUTHORS,
            ScopeInterface::SCOPE_WEBSITE,
            $this->getWebsiteId($storeId)
        );
    }

    /**
     * Get blog title
     *
     * @return string
     */
    public function getBlogTitle()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_BLOG_TITLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get number of posts per page
     *
     * @return int
     */
    public function getNumPostsPerPage()
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_POSTS_PER_PAGE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get quantity of related posts
     *
     * @return int
     */
    public function getQtyOfRelatedPosts()
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_QTY_OF_RELATED_POSTS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get positions of sharing buttons to display
     *
     * @return array
     */
    public function getDisplaySharingAt()
    {
        return explode(
            ',',
            $this->scopeConfig->getValue(self::XML_PATH_DISPLAY_SHARING_AT, ScopeInterface::SCOPE_WEBSITE)
        );
    }

    /**
     * Check if comments are enabled
     *
     * @return bool
     */
    public function isCommentsEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_COMMENTS_ENABLED, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get number of recent posts to display
     *
     * @return int
     */
    public function getNumRecentPosts()
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_RECENT_POSTS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get number of most popular tags to display
     *
     * @return int
     */
    public function getNumPopularTags()
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_POPULAR_TAGS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if popular tags should be highlighted
     *
     * @return bool
     */
    public function isHighlightTags()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_HIGHLIGHT_TAGS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get sidebar CMS block ID
     *
     * @return int
     */
    public function getSidebarCmsBlockId()
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_SIDEBAR_CMS_BLOCK, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get blog meta description
     *
     * @return string
     */
    public function getBlogMetaDescription()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_META_DESCRIPTION, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get blog change frequency
     *
     * @param int $storeId
     * @return string
     */
    public function getSitemapChangeFrequency($storeId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SITEMAP_CHANGEFREQ,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get blog priority
     *
     * @param int $storeId
     * @return string
     */
    public function getSitemapPriority($storeId)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SITEMAP_PRIORITY, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Check if display blog posts tab on product page
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isDisplayPostsOnProductPage($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RELATED_DISPLAY_POSTS_ON_PRODUCT_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get display related products block on post page
     *
     * @param int|null $storeId
     * @return string
     */
    public function getRelatedBlockPosition($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RELATED_BLOCK_POSITION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get display related products block layout
     *
     * @param int|null $storeId
     * @return string
     */
    public function getRelatedBlockLayout($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RELATED_BLOCK_LAYOUT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get max products to display
     *
     * @param int|null $storeId
     * @return int
     */
    public function getRelatedProductsLimit($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RELATED_PRODUCTS_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get display "Add to Cart" button
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isRelatedDisplayAddToCart($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RELATED_DISPLAY_ADD_TO_CART,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get store name
     *
     * @param int|null $storeId
     * @return string
     */
    public function getStoreName($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_STORE_INFORMATION_NAME,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get facebook application ID
     *
     * @param int|null $storeId
     * @return string
     */
    public function getFacebookAppId($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_FACEBOOK_APP_ID,
            ScopeInterface::SCOPE_WEBSITE,
            $this->getWebsiteId($storeId)
        );
    }

    /**
     * Get meta twitter site
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMetaTwitterSite($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_META_TWITTER_SITE,
            ScopeInterface::SCOPE_WEBSITE,
            $this->getWebsiteId($storeId)
        );
    }

    /**
     * Check if category listing visible in sidebar
     *
     * @return int
     */
    public function isDisplaySidebarCategoryListing($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_SIDEBAR_CATEGORY_LISTING_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get sidebar category listing limit
     *
     * @return int
     */
    public function getNumCategoriesToDisplay($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_SIDEBAR_CATEGORY_LISTING_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get seo url type
     *
     * @return string
     */
    public function getSeoUrlType($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SEO_URL_TYPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
