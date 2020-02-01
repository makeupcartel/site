<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Block\Author;
use Aheadworks\Blog\Block\AuthorList;
use Aheadworks\Blog\Block\Author\Listing;
use Aheadworks\Blog\Block\Author\ListingFactory;
use Aheadworks\Blog\Model\Config;
use Magento\Framework\App\Request\Http;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test for \Aheadworks\Blog\Block\AuthorList
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthorListTest extends TestCase
{
    /**#@+
     * Author list constants defined for test
     */
    const AUTHOR_ID = 1;
    const BLOG_TITLE_CONFIG_VALUE = 'Blog';
    const STORE_ID = 1;
    /**#@-*/

    /**
     * @var AuthorList
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
    private $authorListingMock;

    /**
     * @var AuthorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->authorMock = $this->createMock(AuthorInterface::class);
        $this->authorListingMock = $this->createMock(Listing::class);
        $this->authorListingMock->expects($this->any())
            ->method('getAuthors')
            ->will($this->returnValue([$this->authorMock]));
        $authorListingFactoryMock = $this->createMock(ListingFactory::class);
        $authorListingFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->authorListingMock));

        $configMock = $this->createMock(Config::class);
        $configMock->expects($this->any())
            ->method('getBlogTitle')
            ->will($this->returnValue(self::BLOG_TITLE_CONFIG_VALUE));

        $requestMock = $this->createMock(Http::class);
        $requestMock->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        ['author_id', null, self::AUTHOR_ID]
                    ]
                )
            );

        $this->childBlockMock = $this->createMock(Template::class);
        $this->childBlockMock->expects($this->any())
            ->method('setData')
            ->will($this->returnSelf());

        $this->layoutMock = $this->createMock(LayoutInterface::class);
        $this->layoutMock->expects($this->any())
            ->method('getBlock')
            ->will($this->returnValue($this->childBlockMock));
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::STORE_ID));
        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
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
            AuthorList::class,
            [
                'context' => $context,
                'authorListingFactory' => $authorListingFactoryMock,
                'config' => $configMock
            ]
        );
    }

    /**
     * Testing of retrieving of authors
     */
    public function testGetAuthors()
    {
        $this->assertEquals([$this->authorMock], $this->block->getAuthors());
    }

    /**
     * Testing of getItemHtml method
     */
    public function testGetItemHtml()
    {
        $itemHtml = 'item html';
        $this->layoutMock->expects($this->once())
            ->method('createBlock')
            ->with($this->equalTo(Author::class))
            ->willReturn($this->childBlockMock);
        $this->childBlockMock->expects($this->any())
            ->method('toHtml')
            ->willReturn($itemHtml);
        $this->assertEquals($itemHtml, $this->block->getItemHtml($this->authorMock));
    }
}
