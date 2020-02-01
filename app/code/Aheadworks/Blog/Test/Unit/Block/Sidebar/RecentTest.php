<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Sidebar;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Block\Sidebar\Recent;
use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Blog\Block\Post\Listing;
use Aheadworks\Blog\Block\Post\ListingFactory;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Model\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Test for \Aheadworks\Blog\Block\Sidebar\Recent
 */
class RecentTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Recent constants defined for test
     */
    const STORE_ID = 1;
    const RECENT_POSTS_CONFIG_VALUE = 5;
    /**#@-*/

    /**
     * @var Recent
     */
    private $block;

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

        $searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['setPageSize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->postMock = $this->getMockForAbstractClass(PostInterface::class);
        $postListingMock = $this->getMockBuilder(Listing::class)
            ->setMethods(['getPosts', 'getSearchCriteriaBuilder'])
            ->disableOriginalConstructor()
            ->getMock();
        $postListingMock->expects($this->any())
            ->method('getPosts')
            ->will($this->returnValue([$this->postMock]));
        $postListingMock->expects($this->any())
            ->method('getSearchCriteriaBuilder')
            ->will($this->returnValue($searchCriteriaBuilderMock));
        $postListingFactoryMock = $this->getMockBuilder(ListingFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $postListingFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($postListingMock));

        $postRepositoryMock = $this->getMockForAbstractClass(PostRepositoryInterface::class);

        $configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getNumRecentPosts'])
            ->disableOriginalConstructor()
            ->getMock();
        $configMock->expects($this->any())
            ->method('getNumRecentPosts')
            ->will($this->returnValue(self::RECENT_POSTS_CONFIG_VALUE));

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['post_id', null, null],
                        ['blog_category_id', null, null]
                    ]
                )
            );

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
                'storeManager' => $storeManagerMock
            ]
        );

        $this->block = $objectManager->getObject(
            Recent::class,
            [
                'context' => $context,
                'postRepository' => $postRepositoryMock,
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
}
