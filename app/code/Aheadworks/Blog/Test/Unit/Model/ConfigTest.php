<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model;

use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Source\Config\Related\BlockLayout;
use Aheadworks\Blog\Model\Source\Config\Related\BlockPosition;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Blog\Model\Config
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for store and website config scopes testing
     */
    const WEBSITE_ID = 4512;
    const STORE_ID = 2514;
    /**#@-*/

    /**
     * @var Config
     */
    private $configModel;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->configModel = $objectManager->getObject(
            Config::class,
            ['scopeConfig' => $this->scopeConfigMock, 'storeManager' => $this->storeManagerMock]
        );
    }

    /**
     * Test get "is blog enabled" config value
     *
     * @param bool $value
     * @dataProvider boolValuesDataProvider
     */
    public function testIsBlogEnabled($value)
    {
        $this->storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(self::WEBSITE_ID);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_ENABLED, ScopeInterface::SCOPE_WEBSITE, self::WEBSITE_ID)
            ->willReturn($value);
        $this->assertSame($value, $this->configModel->isBlogEnabled(self::STORE_ID));
    }

    /**
     * Test get "is nav menu link enabled" config value
     *
     * @param bool $value
     * @dataProvider boolValuesDataProvider
     */
    public function testSsMenuLinkEnabled($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_NAVIGATION_MENU_LINK_ENABLED, ScopeInterface::SCOPE_STORE, self::STORE_ID)
            ->willReturn($value);
        $this->assertSame($value, $this->configModel->isMenuLinkEnabled(self::STORE_ID));
    }

    /**
     * Test get route to blog
     */
    public function testGetRouteToBlog()
    {
        $this->storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(self::WEBSITE_ID);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $routeToBlog = 'blog';
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_ROUTE_TO_BLOG, ScopeInterface::SCOPE_WEBSITE, self::WEBSITE_ID)
            ->willReturn($routeToBlog);
        $this->assertEquals($routeToBlog, $this->configModel->getRouteToBlog(self::STORE_ID));
    }

    /**
     * Test get route to authors
     */
    public function testGetRouteToAuthors()
    {
        $this->storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(self::WEBSITE_ID);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $route = 'authors';
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_ROUTE_TO_AUTHORS, ScopeInterface::SCOPE_WEBSITE, self::WEBSITE_ID)
            ->willReturn($route);
        $this->assertEquals($route, $this->configModel->getRouteToAuthors(self::STORE_ID));
    }

    /**
     * Test get blog title
     */
    public function testGetBlogTitle()
    {
        $blogTitle = 'Blog';
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_BLOG_TITLE, ScopeInterface::SCOPE_STORE)
            ->willReturn($blogTitle);
        $this->assertEquals($blogTitle, $this->configModel->getBlogTitle());
    }

    /**
     * Test get number of posts per page
     */
    public function testGetNumPostsPerPage()
    {
        $postsPerPage = 5;
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_POSTS_PER_PAGE, ScopeInterface::SCOPE_STORE)
            ->willReturn($postsPerPage);
        $this->assertSame($postsPerPage, $this->configModel->getNumPostsPerPage());
    }

    /**
     * Test get qty of related posts
     */
    public function testGetQtyOfRelatedPosts()
    {
        $postsPerPage = 3;
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_QTY_OF_RELATED_POSTS, ScopeInterface::SCOPE_STORE)
            ->willReturn($postsPerPage);
        $this->assertSame($postsPerPage, $this->configModel->getQtyOfRelatedPosts());
    }

    /**
     * Test get positions of sharing buttons to display
     *
     * @dataProvider getDisplaySharingAtDataProvider
     */
    public function testGetDisplaySharingAt($value, $expected)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_DISPLAY_SHARING_AT, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn($value);
        $this->assertEquals($expected, $this->configModel->getDisplaySharingAt());
    }

    /**
     * Test get "is comments enabled" config value
     *
     * @dataProvider boolValuesDataProvider
     */
    public function testIsCommentsEnabled($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_COMMENTS_ENABLED, ScopeInterface::SCOPE_WEBSITE)
            ->willReturn($value);
        $this->assertSame($value, $this->configModel->isCommentsEnabled());
    }

    /**
     * Test get number of recent posts
     */
    public function testGetNumRecentPosts()
    {
        $numRecentPosts = 10;
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_RECENT_POSTS, ScopeInterface::SCOPE_STORE)
            ->willReturn($numRecentPosts);
        $this->assertSame($numRecentPosts, $this->configModel->getNumRecentPosts());
    }

    /**
     * Test get number of popular tags
     */
    public function testGetNumPopularTags()
    {
        $numPopularTags = 5;
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_POPULAR_TAGS, ScopeInterface::SCOPE_STORE)
            ->willReturn($numPopularTags);
        $this->assertSame($numPopularTags, $this->configModel->getNumPopularTags());
    }

    /**
     * Test get "is highlight tags" config value
     *
     * @dataProvider boolValuesDataProvider
     */
    public function testIsHighlightTags($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_HIGHLIGHT_TAGS, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->assertSame($value, $this->configModel->isHighlightTags());
    }

    /**
     * Test get sidebar CMS block ID
     */
    public function testGetSidebarCmsBlockId()
    {
        $cmsBlockId = 1;
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_SIDEBAR_CMS_BLOCK, ScopeInterface::SCOPE_STORE)
            ->willReturn($cmsBlockId);
        $this->assertSame($cmsBlockId, $this->configModel->getSidebarCmsBlockId());
    }

    /**
     * Test get block meta description
     */
    public function testGetBlogMetaDescription()
    {
        $metaDescription = 'Blog meta description';
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_META_DESCRIPTION, ScopeInterface::SCOPE_STORE)
            ->willReturn($metaDescription);
        $this->assertEquals($metaDescription, $this->configModel->getBlogMetaDescription());
    }

    /**
     * Test get blog change frequency
     */
    public function testGetSitemapChangeFrequency()
    {
        $storeId = 1;
        $changeFreq = 'weekly';
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_SITEMAP_CHANGEFREQ, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($changeFreq);
        $this->assertEquals($changeFreq, $this->configModel->getSitemapChangeFrequency($storeId));
    }

    /**
     * Test get blog priority
     */
    public function testGetSitemapPriority()
    {
        $storeId = 1;
        $priority = '0.5';
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_SITEMAP_PRIORITY, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($priority);
        $this->assertEquals($priority, $this->configModel->getSitemapPriority($storeId));
    }

    /**
     * Testing of isDisplayPostsOnProductPage method
     */
    public function testIsDisplayPostsOnProductPage()
    {
        $storeId = 1;
        $value = 1;

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_RELATED_DISPLAY_POSTS_ON_PRODUCT_PAGE, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($value);
        $this->assertEquals($value, $this->configModel->isDisplayPostsOnProductPage($storeId));
    }

    /**
     * Testing of getRelatedBlockPosition method
     */
    public function testGetRelatedBlockPosition()
    {
        $storeId = 1;
        $value = BlockPosition::AFTER_COMMENTS;

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_RELATED_BLOCK_POSITION, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($value);
        $this->assertEquals($value, $this->configModel->getRelatedBlockPosition($storeId));
    }

    /**
     * Testing of getRelatedBlockLayout method
     */
    public function testGetRelatedBlockLayout()
    {
        $storeId = 1;
        $value = BlockLayout::MULTIPLE_ROWS_VALUE;

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_RELATED_BLOCK_LAYOUT, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($value);
        $this->assertEquals($value, $this->configModel->getRelatedBlockLayout($storeId));
    }

    /**
     * Testing of getRelatedProductsLimit method
     */
    public function testGetRelatedProductsLimit()
    {
        $storeId = 1;
        $value = 5;

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_RELATED_PRODUCTS_LIMIT, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($value);
        $this->assertEquals($value, $this->configModel->getRelatedProductsLimit($storeId));
    }

    /**
     * Testing of isRelatedDisplayAddToCart method
     */
    public function testIsRelatedDisplayAddToCart()
    {
        $storeId = 1;
        $value = 1;

        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_RELATED_DISPLAY_ADD_TO_CART, ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn($value);
        $this->assertEquals($value, $this->configModel->isRelatedDisplayAddToCart($storeId));
    }

    /**
     * Test get store name
     */
    public function testGetStoreName()
    {
        $storeName = 'Test store name';
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_STORE_INFORMATION_NAME, ScopeInterface::SCOPE_STORE, self::STORE_ID)
            ->willReturn($storeName);
        $this->assertEquals($storeName, $this->configModel->getStoreName(self::STORE_ID));
    }

    /**
     * Test get facebook application ID
     */
    public function testGetFacebookAppId()
    {
        $fbAppId = '1234567890';
        $this->storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(self::WEBSITE_ID);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_FACEBOOK_APP_ID, ScopeInterface::SCOPE_WEBSITE, self::WEBSITE_ID)
            ->willReturn($fbAppId);
        $this->assertEquals($fbAppId, $this->configModel->getFacebookAppId(self::STORE_ID));
    }

    /**
     * Test get twitter site name
     */
    public function testGetMetaTwitterSite()
    {
        $twitterSite = '@mysite';
        $this->storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn(self::WEBSITE_ID);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_META_TWITTER_SITE, ScopeInterface::SCOPE_WEBSITE, self::WEBSITE_ID)
            ->willReturn($twitterSite);
        $this->assertEquals($twitterSite, $this->configModel->getMetaTwitterSite(self::STORE_ID));
    }

    /**
     * Test get "is Display Sidebar Category Listing" config value
     *
     * @dataProvider boolValuesDataProvider
     */
    public function testIsDisplaySidebarCategoryListing($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_SIDEBAR_CATEGORY_LISTING_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->assertEquals($value, $this->configModel->isDisplaySidebarCategoryListing(self::STORE_ID));
    }

    /**
     * Test get number of categories to display in sidebar
     */
    public function testGetNumCategoriesToDisplay()
    {
        $categoriesToDisplay = 5;
        $this->scopeConfigMock->expects($this->any())
            ->method('getValue')
            ->with(Config::XML_PATH_SIDEBAR_CATEGORY_LISTING_LIMIT, ScopeInterface::SCOPE_STORE)
            ->willReturn($categoriesToDisplay);
        $this->assertSame($categoriesToDisplay, $this->configModel->getNumCategoriesToDisplay(self::STORE_ID));
    }

    /**
     * Data provider for testIsHighlightTags method
     *
     * @return array
     */
    public function boolValuesDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * Data provider for testGetDisplaySharingAt method
     *
     * @return array
     */
    public function getDisplaySharingAtDataProvider()
    {
        return [
            ['value', ['value']],
            ['value1,value2', ['value1', 'value2']]
        ];
    }
}
