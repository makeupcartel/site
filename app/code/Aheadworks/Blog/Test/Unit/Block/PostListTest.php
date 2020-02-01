<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Block\PostList;
use Aheadworks\Blog\Block\Post\Listing;
use Aheadworks\Blog\Block\Post\ListingFactory;
use Aheadworks\Blog\Model\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test for \Aheadworks\Blog\Block\PostList
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PostListTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Post list constants defined for test
     */
    const POST_ID = 1;
    const STORE_ID = 1;
    const BLOG_TITLE_CONFIG_VALUE = 'Blog';
    const POSTS_PER_PAGE_CONFIG_VALUE = 5;
    /**#@-*/

    /**
     * @var PostList
     */
    private $block;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var Template|\PHPUnit_Framework_MockObject_MockObject
     */
    private $childBlockMock;

    /**
     * @var Listing|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postListingMock;

    /**
     * @var PostInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postMock;

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
        $this->postListingMock = $this->getMockBuilder(Listing::class)
            ->setMethods(['getPosts', 'applyPagination'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->postListingMock->expects($this->any())
            ->method('getPosts')
            ->will($this->returnValue([$this->postMock]));
        $postListingFactoryMock = $this->getMockBuilder(ListingFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $postListingFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->postListingMock));

        $configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getBlogTitle', 'getNumPostsPerPage'])
            ->disableOriginalConstructor()
            ->getMock();
        $configMock->expects($this->any())
            ->method('getBlogTitle')
            ->will($this->returnValue(self::BLOG_TITLE_CONFIG_VALUE));
        $configMock->expects($this->any())
            ->method('getNumPostsPerPage')
            ->will($this->returnValue(self::POSTS_PER_PAGE_CONFIG_VALUE));

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['post_id', null, self::POST_ID],
                        ['blog_category_id', null, null],
                        ['tag', null, null]
                    ]
                )
            );

        $this->childBlockMock = $this->getMockBuilder(Template::class)
            ->setMethods(['toHtml', 'setPath', 'setLimit'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->childBlockMock->expects($this->any())
            ->method('setPath')
            ->will($this->returnSelf());
        $this->childBlockMock->expects($this->any())
            ->method('setLimit')
            ->will($this->returnSelf());

        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->layoutMock->expects($this->any())
            ->method('getBlock')
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
                'layout' => $this->layoutMock,
                'storeManager' => $storeManagerMock
            ]
        );

        $this->block = $objectManager->getObject(
            PostList::class,
            [
                'context' => $context,
                'postListingFactory' => $postListingFactoryMock,
                'config' => $configMock
            ]
        );
    }

    /**
     * Testing of retrieving of posts
     */
    public function testGetPosts()
    {
        $this->assertEquals([$this->postMock], $this->block->getPosts());
    }

    /**
     * Testing of getItemHtml method
     */
    public function testGetItemHtml()
    {
        $itemHtml = 'item html';
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo(\Aheadworks\Blog\Block\Post::class))
            ->willReturn($this->childBlockMock);
        $this->childBlockMock->expects($this->any())
            ->method('toHtml')
            ->willReturn($itemHtml);
        $this->assertEquals($itemHtml, $this->block->getItemHtml($this->postMock));
    }
}
