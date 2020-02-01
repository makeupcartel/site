<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Api\Data\CategorySearchResultsInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Block\Link;
use Aheadworks\Blog\Block\LinkFactory;
use Aheadworks\Blog\Block\Post;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Source\Config\Related\BlockPosition;
use Aheadworks\Blog\Model\Template\FilterProvider;
use Aheadworks\Blog\Model\Source\Post\SharingButtons\DisplayAt as DisplaySharingAt;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\Template as FilterTemplate;
use Magento\Framework\View\Element\Template as ElementTemplate;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Blog\Model\Source\Config\Seo\UrlType;

/**
 * Test for \Aheadworks\Blog\Block\Post
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Post constants defined for test
     */
    const POST_ID = 1;
    const POST_TITLE = 'Post';
    const CATEGORY_NAME = 'Category';
    const DISCUS_FORUM_CODE = 'disqus_forum_code';
    const POST_URL = 'http://localhost/post';
    const CATEGORY_URL = 'http://localhost/cat';
    const CATEGORY_LINK_HTML = '<a href="http://localhost/cat">Category</a>';
    const SOCIAL_ICONS_HTML = 'social icons html';
    const STORE_ID = 1;
    /**#@-*/

    /**
     * @var array
     */
    private $postCategoryIds = [1, 2];

    /**
     * @var Post
     */
    private $block;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var FilterTemplate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var ElementTemplate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $childBlockMock;

    /**
     * @var PostInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postMock;

    /**
     * @var CategoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->postMock = $this->getMockForAbstractClass(PostInterface::class);
        $this->postMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue(self::POST_TITLE));
        $this->postMock->expects($this->any())
            ->method('getCategoryIds')
            ->will($this->returnValue($this->postCategoryIds));
        $postRepositoryMock = $this->getMockForAbstractClass(PostRepositoryInterface::class);
        $postRepositoryMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo(self::POST_ID))
            ->will($this->returnValue($this->postMock));

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderMock->expects($this->any())
            ->method('addFilter')
            ->will($this->returnSelf());
        $searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($searchCriteriaMock));

        $this->categoryMock = $this->getMockForAbstractClass(CategoryInterface::class);
        $this->categoryMock->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::CATEGORY_NAME));
        $categorySearchResultsMock = $this->getMockForAbstractClass(CategorySearchResultsInterface::class);
        $categorySearchResultsMock->expects($this->any())
            ->method('getItems')
            ->will($this->returnValue([$this->categoryMock]));
        $categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $categoryRepositoryMock->expects($this->any())
            ->method('getList')
            ->with($this->equalTo($searchCriteriaMock))
            ->will($this->returnValue($categorySearchResultsMock));

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isCommentsEnabled', 'getDisplaySharingAt', 'getRelatedBlockPosition', 'getSeoUrlType'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock->expects($this->any())->method('getSeoUrlType')
            ->with(self::STORE_ID)
            ->will($this->returnValue(UrlType::URL_EXC_CATEGORY));

        $urlMock = $this->getMockBuilder(Url::class)
            ->setMethods(['getPostUrl', 'getCategoryUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $urlMock->expects($this->any())->method('getPostUrl')
            ->with($this->equalTo($this->postMock))
            ->will($this->returnValue(self::POST_URL));
        $urlMock->expects($this->any())->method('getCategoryUrl')
            ->with($this->equalTo($this->categoryMock))
            ->will($this->returnValue(self::CATEGORY_URL));

        $linkMock = $this->getMockBuilder(Link::class)
            ->setMethods(['setHref', 'setTitle', 'setLabel', 'toHtml'])
            ->disableOriginalConstructor()
            ->getMock();
        $linkMock->expects($this->any())->method('setHref')->will($this->returnSelf());
        $linkMock->expects($this->any())->method('setTitle')->will($this->returnSelf());
        $linkMock->expects($this->any())->method('setLabel')->will($this->returnSelf());
        $linkMock->expects($this->any())
            ->method('toHtml')
            ->will($this->returnValue(self::CATEGORY_LINK_HTML));
        $linkFactoryMock = $this->getMockBuilder(LinkFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $linkFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($linkMock));

        $this->filterMock = $this->getMockBuilder(FilterTemplate::class)
            ->setMethods(['setStoreId', 'filter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->getMockBuilder(FilterTemplate::class)
            ->setMethods(['setStoreId', 'filter'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterMock->expects($this->any())
            ->method('setStoreId')
            ->will($this->returnSelf());
        $templateFilterProviderMock = $this->getMockBuilder(FilterProvider::class)
            ->setMethods(['getFilter'])
            ->disableOriginalConstructor()
            ->getMock();
        $templateFilterProviderMock->expects($this->any())
            ->method('getFilter')
            ->will($this->returnValue($this->filterMock));
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['post_id', null, self::POST_ID],
                        ['blog_category_id', null, null]
                    ]
                )
            );

        $this->childBlockMock = $this->getMockBuilder(ElementTemplate::class)
            ->setMethods(
                [
                    'setTemplate',
                    'setShareUrl',
                    'setSharingText',
                    'setPageIdentifier',
                    'setPageUrl',
                    'setPageTitle',
                    'toHtml'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->childBlockMock->expects($this->any())->method('setTemplate')->will($this->returnSelf());
        $this->childBlockMock->expects($this->any())->method('setShareUrl')->will($this->returnSelf());
        $this->childBlockMock->expects($this->any())->method('setSharingText')->will($this->returnSelf());
        $this->childBlockMock->expects($this->any())->method('setPageIdentifier')->will($this->returnSelf());
        $this->childBlockMock->expects($this->any())->method('setPageUrl')->will($this->returnSelf());
        $this->childBlockMock->expects($this->any())->method('setPageTitle')->will($this->returnSelf());

        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $layoutMock->expects($this->any())
            ->method('getChildName')
            ->will($this->returnValue('child.name'));
        $layoutMock->expects($this->any())
            ->method('getBlock')
            ->will($this->returnValue($this->childBlockMock));
        $layoutMock->expects($this->any())
            ->method('createBlock')
            ->will($this->returnValue($this->childBlockMock));

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::STORE_ID));
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));

        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $requestMock,
                'layout' => $layoutMock,
                'storeManager' => $storeManagerMock
            ]
        );

        $this->block = $objectManager->getObject(
            Post::class,
            [
                'context' => $context,
                'categoryRepository' => $categoryRepositoryMock,
                'postRepository' => $postRepositoryMock,
                'searchCriteriaBuilder' => $searchCriteriaBuilderMock,
                'url' => $urlMock,
                'config' => $this->configMock,
                'templateFilterProvider' => $templateFilterProviderMock,
                'linkFactory' => $linkFactoryMock
            ]
        );
    }

    /**
     * Testing that a list item mode is checked correctly
     *
     * @dataProvider isListItemModeDataProvider
     * @param string $mode
     * @param bool $expectedResult
     */
    public function testIsListItemMode($mode, $expectedResult)
    {
        $this->block->setMode($mode);
        $this->assertEquals($expectedResult, $this->block->isListItemMode());
    }

    /**
     * Testing that a view mode is checked correctly
     *
     * @dataProvider isViewModeDataProvider
     * @param string $mode
     * @param bool $expectedResult
     */
    public function testIsViewMode($mode, $expectedResult)
    {
        $this->block->setMode($mode);
        $this->assertEquals($expectedResult, $this->block->isViewMode());
    }

    /**
     * Testing of retrieving of post's categories
     */
    public function testGetCategories()
    {
        $class = new \ReflectionClass($this->block);
        $method = $class->getMethod('getCategories');
        $method->setAccessible(true);
        $this->assertEquals([$this->categoryMock], $method->invoke($this->block));
    }

    /**
     * Testing of commentsEnabled method
     *
     * @dataProvider commentsEnabledDataProvider
     * @param bool $isCommentsEnabled
     * @param bool $isAllowComments
     * @param bool $expectedResult
     */
    public function testCommentsEnabled($isCommentsEnabled, $isAllowComments, $expectedResult)
    {
        $this->configMock->expects($this->any())
            ->method('isCommentsEnabled')
            ->willReturn($isCommentsEnabled);
        $this->postMock->expects($this->any())
            ->method('getIsAllowComments')
            ->willReturn($isAllowComments);
        $this->assertEquals($expectedResult, $this->block->commentsEnabled());
    }

    /**
     * Testing of showReadMoreButton method
     *
     * @dataProvider showReadMoreButtonDataProvider
     * @param string $mode
     * @param string $shortContent
     * @param bool $expectedResult
     */
    public function testShowReadMoreButton($mode, $shortContent, $expectedResult)
    {
        $this->block->setMode($mode);
        $this->postMock->expects($this->any())
            ->method('getShortContent')
            ->will($this->returnValue($shortContent));
        $this->assertEquals($expectedResult, $this->block->showReadMoreButton($this->postMock));
    }

    /**
     * Testing of retrieving of social icons html
     *
     * @dataProvider getSocialIconsHtmlDataProvider
     * @param int[] $displayAt
     * @param string $mode
     * @param string $expected
     */
    public function testGetSocialIconsHtml($displayAt, $mode, $expected)
    {
        $this->configMock->expects($this->any())
            ->method('getDisplaySharingAt')
            ->willReturn($displayAt);
        $this->block->setMode($mode);
        $this->childBlockMock->expects($this->any())
            ->method('toHtml')
            ->willReturn(self::SOCIAL_ICONS_HTML);
        $this->assertEquals($expected, $this->block->getSocialIconsHtml());
    }

    /**
     * Testing of retrieving array of category links html
     */
    public function testGetCategoryLinks()
    {
        $this->assertEquals([self::CATEGORY_LINK_HTML], $this->block->getCategoryLinks());
    }

    /**
     * Testing of retrieving of Disqus embed html
     */
    public function testGetDisqusEmbedHtml()
    {
        $disqusEmbedHtml = 'disqus html';
        $this->childBlockMock->expects($this->any())
            ->method('toHtml')
            ->willReturn($disqusEmbedHtml);
        $this->assertEquals($disqusEmbedHtml, $this->block->getDisqusEmbedHtml());
    }

    /**
     * Testing of retrieving of related product html
     *
     * @param bool $expected
     * @param bool $viewMode
     * @param string $position
     * @dataProvider getRelatedProductHtmlDataProvider
     */
    public function testGetRelatedProductHtml($expected, $viewMode, $position)
    {
        $this->childBlockMock->expects($this->once())
            ->method('toHtml')
            ->willReturn($expected);
        $this->configMock->expects($this->once())
            ->method('getRelatedBlockPosition')
            ->willReturn($position);
        $this->assertEquals($expected, $this->block->getRelatedProductHtml($viewMode, $position));
    }

    /**
     * Testing whether featured image is loaded or not
     *
     * @param $imagePath
     * @param $expectedResult
     * @dataProvider isFeaturedImageLoadedDataProvider
     */
    public function testIsFeaturedImageLoaded($imagePath, $expectedResult)
    {
        $this->postMock->expects($this->any())
            ->method('getFeaturedImageFile')
            ->willReturn($imagePath);
        $this->assertEquals($expectedResult, $this->block->isFeaturedImageLoaded());
    }

    /**
     * Data provider for testGetRelatedProductHtml method
     *
     * @return array
     */
    public function getRelatedProductHtmlDataProvider()
    {
        return [
            ['related product html', true, BlockPosition::AFTER_POST],
            ['', true, BlockPosition::AFTER_COMMENTS]
        ];
    }

    /**
     * Testing of getContent method
     *
     * @dataProvider getContentDataProvider
     * @param string $content
     * @param string $shortContent
     * @param string $mode
     * @param string $expectedResult
     */
    public function testGetContent($content, $shortContent, $mode, $expectedResult)
    {
        $this->postMock->expects($this->any())
            ->method('getContent')
            ->willReturn($content);
        $this->postMock->expects($this->any())
            ->method('getShortContent')
            ->willReturn($shortContent);
        $this->block->setMode($mode);
        $this->filterMock->expects($this->atLeastOnce())
            ->method('filter')
            ->with($this->equalTo($expectedResult))
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->block->getContent($this->postMock));
    }

    /**
     * Data provider for testIsListItemMode method
     *
     * @return array
     */
    public function isListItemModeDataProvider()
    {
        return [
            'list item mode' => [Post::MODE_LIST_ITEM, true],
            'view mode' => [Post::MODE_VIEW, false]
        ];
    }

    /**
     * Data provider for testIsViewMode method
     *
     * @return array
     */
    public function isViewModeDataProvider()
    {
        return [
            'view mode' => [Post::MODE_VIEW, true],
            'list item mode' => [Post::MODE_LIST_ITEM, false]
        ];
    }

    /**
     * Data provider for testCommentsEnabled method
     *
     * @return array
     */
    public function commentsEnabledDataProvider()
    {
        return [
            'comments enabled, commenting is allowed for post' => [true, true, true],
            'comments disabled, commenting is allowed for post' => [false, true, false],
            'comments enabled, commenting is not allowed for post' => [true, false, false],
            'comments disabled, commenting is not allowed for post' => [false, false, false]
        ];
    }

    /**
     * Data provider for testShowReadMoreButton method
     *
     * @return array
     */
    public function showReadMoreButtonDataProvider()
    {
        return [
            'list item mode, post has short content' => [Post::MODE_LIST_ITEM, 'short content', true],
            'list item mode, post has not short content' => [Post::MODE_LIST_ITEM, null, false],
            'view mode' => [Post::MODE_VIEW, null, false]
        ];
    }

    /**
     * Data provider for testGetSocialIconsHtml method
     *
     * @return array
     */
    public function getSocialIconsHtmlDataProvider()
    {
        return [
            [[DisplaySharingAt::POST], Post::MODE_VIEW, self::SOCIAL_ICONS_HTML],
            [[DisplaySharingAt::POST], Post::MODE_LIST_ITEM, ''],
            [[DisplaySharingAt::POST_LIST], Post::MODE_LIST_ITEM, self::SOCIAL_ICONS_HTML],
            [[DisplaySharingAt::POST_LIST], Post::MODE_VIEW, '']
        ];
    }

    /**
     * Data provider for testGetContent method
     *
     * @return array
     */
    public function getContentDataProvider()
    {
        return [
            'view mode' => ['content', 'short content', Post::MODE_VIEW, 'content'],
            'list item mode, post has short content' => [
                'content',
                'short content',
                Post::MODE_LIST_ITEM,
                'short content'
            ],
            'list item mode, post has not short content' => ['content', null, Post::MODE_LIST_ITEM, 'content']
        ];
    }

    /**
     * Data provider for testIsFeaturedImageLoaded method
     *
     * @return array
     */
    public function isFeaturedImageLoadedDataProvider()
    {
        return [
            'image exists' => ['wysiwyg/test.png', true],
            'image does not exist' => ['', false],
        ];
    }
}
