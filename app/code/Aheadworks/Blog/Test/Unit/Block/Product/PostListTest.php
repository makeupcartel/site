<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Product;

use Magento\Framework\App\RequestInterface;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Blog\Model\Url;
use Aheadworks\Blog\Block\Post\Listing as PostListing;
use Aheadworks\Blog\Block\Product\PostList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Test for \Aheadworks\Blog\Block\Product\PostList
 */
class PostListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PostList
     */
    private $block;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var PostListing|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postListingMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isDisplayPostsOnProductPage'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlMock = $this->getMockBuilder(Url::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->postListingMock = $this->getMockBuilder(PostListing::class)
            ->setMethods(['getSearchCriteriaBuilder', 'getPosts'])
            ->disableOriginalConstructor()
            ->getMock();

        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock
            ]
        );

        $this->block = $objectManager->getObject(
            PostList::class,
            [
                'context'     => $context,
                'config'      => $this->configMock,
                'url'         => $this->urlMock,
                'postListing' => $this->postListingMock
            ]
        );
    }

    /**
     * Testing of getPosts method
     *
     * @param bool  $isDisplay
     * @param array $expected
     * @dataProvider getPostsDataProvider
     */
    public function testGetPosts($isDisplay, $expected)
    {
        $this->configMock->expects($this->once())
            ->method('isDisplayPostsOnProductPage')
            ->willReturn($isDisplay);

        if ($isDisplay) {
            $productId = 1;

            $this->requestMock->expects($this->once())
                ->method('getParam')
                ->with('id')
                ->willReturn($productId);

            $searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
                ->setMethods(['addFilter'])
                ->disableOriginalConstructor()
                ->getMock();
            $searchCriteriaBuilderMock->expects($this->once())
                ->method('addFilter')
                ->with('product_id', $productId);
            $this->postListingMock->expects($this->once())
                ->method('getPosts')
                ->willReturn($expected);
            $this->postListingMock->expects($this->once())
                ->method('getSearchCriteriaBuilder')
                ->willReturn($searchCriteriaBuilderMock);
        }

        $this->assertEquals($expected, $this->block->getPosts());
    }

    /**
     * Data provider for testGetPosts method
     *
     * @return array
     */
    public function getPostsDataProvider()
    {
        $postMock1 = $this->getMockForAbstractClass(PostInterface::class);
        $postMock2 = $this->getMockForAbstractClass(PostInterface::class);
        return [
            [false, []],
            [true, [$postMock1, $postMock2]]
        ];
    }

    /**
     * Testing of getPostUrl method
     */
    public function testGetPostUrl()
    {
        $url = 'https://ecommerce.aheadworks.com/';
        $postMock = $this->getMockForAbstractClass(PostInterface::class);

        $this->urlMock->expects($this->once())
            ->method('getPostUrl')
            ->with($postMock)
            ->willReturn($url);

        $this->assertEquals($url, $this->block->getPostUrl($postMock));
    }
}
