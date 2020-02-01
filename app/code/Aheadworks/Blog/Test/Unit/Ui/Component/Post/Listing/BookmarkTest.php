<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Ui\Component\Post\Listing;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Ui\Component\Post\Listing\Bookmark;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Authorization\Model\UserContextInterface;
use Aheadworks\Blog\Model\Serialize\SerializeInterface;
use Aheadworks\Blog\Model\Serialize\Factory as SerializeFactory;

/**
 * Test for \Aheadworks\Blog\Ui\Component\Post\Listing\Bookmark
 */
class BookmarkTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Bookmarks constants defined for test
     */
    const VIEW_INDEX = 'view_index';
    const VIEW_TITLE = 'View';
    const USER_ID = 1;
    /**#@-*/

    /**
     * @var array
     */
    private $changeColumns = ['title' => ['sorting' => 'asc']];

    /**
     * @var array
     */
    private $filters = ['title' => ['like' => 'Vi']];

    /**
     * @var Bookmark
     */
    private $bookmarkComponent;

    /**
     * @var BookmarkInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bookmarkMock;

    /**
     * @var BookmarkRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bookmarkRepositoryMock;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SerializeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->serializerMock = $this->getMockForAbstractClass(SerializeInterface::class);
        $serializeFactoryMock = $this->createPartialMock(SerializeFactory::class, ['create']);
        $serializeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->serializerMock);
        $processorMock = $this->getMockBuilder(Processor::class)
            ->setMethods(['register'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockForAbstractClass(ContextInterface::class);
        $this->contextMock->expects($this->any())
            ->method('getProcessor')
            ->will($this->returnValue($processorMock));

        $this->bookmarkMock = $this->getMockForAbstractClass(BookmarkInterface::class);
        $this->bookmarkMock->expects($this->any())
            ->method('setUserId')
            ->will($this->returnSelf());
        $this->bookmarkMock->expects($this->any())
            ->method('setNamespace')
            ->will($this->returnSelf());
        $this->bookmarkMock->expects($this->any())
            ->method('setIdentifier')
            ->will($this->returnSelf());
        $this->bookmarkMock->expects($this->any())
            ->method('setTitle')
            ->will($this->returnSelf());
        $this->bookmarkMock->expects($this->any())
            ->method('setConfig')
            ->will($this->returnSelf());

        $bookmarkFactoryMock = $this->getMockBuilder(BookmarkInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $bookmarkFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->bookmarkMock));

        $userContextMock = $this->getMockForAbstractClass(UserContextInterface::class);
        $userContextMock->expects($this->any())
            ->method('getUserId')
            ->will($this->returnValue(self::USER_ID));

        $this->bookmarkRepositoryMock = $this->getMockForAbstractClass(BookmarkRepositoryInterface::class);

        $this->bookmarkComponent = $this->objectManager->getObject(
            Bookmark::class,
            [
                'bookmarkFactory' => $bookmarkFactoryMock,
                'userContext' => $userContextMock,
                'context' => $this->contextMock,
                'bookmarkRepository' => $this->bookmarkRepositoryMock,
                'data' => ['config' => []],
                'serializeFactory' => $serializeFactoryMock
            ]
        );
    }

    /**
     * Testing of prepare component configuration
     */
    public function testPrepare()
    {
        $bookmarkMock = $this->getMockBuilder(Bookmark::class)
            ->setMethods(['addView'])
            ->setConstructorArgs(
                $this->objectManager->getConstructArguments(
                    Bookmark::class,
                    ['context' => $this->contextMock]
                )
            )
            ->enableOriginalConstructor()
            ->getMock();
        $bookmarkMock->expects($this->exactly(3))
            ->method('addView')
            ->withConsecutive(
                [$this->equalTo('default')],
                [$this->equalTo('drafts')],
                [$this->equalTo('scheduled')]
            );
        $bookmarkMock->prepare();
    }

    /**
     * Testing of adding view to the current config
     */
    public function testAddViewToConfig()
    {
        $this->bookmarkComponent->addView(
            self::VIEW_INDEX,
            self::VIEW_TITLE,
            $this->changeColumns,
            $this->filters
        );
        $config = $this->bookmarkComponent->getData('config');
        $this->assertArrayHasKey('views', $config);
        $this->assertArrayHasKey(self::VIEW_INDEX, $config['views']);
        $this->assertArrayHasKey('index', $config['views'][self::VIEW_INDEX]);
        $this->assertArrayHasKey('label', $config['views'][self::VIEW_INDEX]);
        $this->assertEquals(self::VIEW_INDEX, $config['views'][self::VIEW_INDEX]['index']);
        $this->assertEquals(self::VIEW_TITLE, $config['views'][self::VIEW_INDEX]['label']);
        $this->assertEquals(
            'asc',
            $config['views'][self::VIEW_INDEX]['data']['columns']['title']['sorting']
        );
        $this->assertArrayHasKey(
            'title',
            $config['views'][self::VIEW_INDEX]['data']['filters']['applied']
        );
        $this->assertEquals(
            ['like' => 'Vi'],
            $config['views'][self::VIEW_INDEX]['data']['filters']['applied']['title']
        );
        $this->serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturn('');
    }

    /**
     * Testing that the added view is saved
     */
    public function testAddViewSave()
    {
        $this->bookmarkMock->expects($this->atLeastOnce())
            ->method('setUserId')
            ->with($this->equalTo(self::USER_ID));
        $this->bookmarkMock->expects($this->atLeastOnce())
            ->method('setIdentifier')
            ->with($this->equalTo(self::VIEW_INDEX));
        $this->bookmarkMock->expects($this->atLeastOnce())
            ->method('setTitle')
            ->with($this->equalTo(self::VIEW_TITLE));
        $this->bookmarkRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->equalTo($this->bookmarkMock));
        $this->bookmarkComponent->addView(
            self::VIEW_INDEX,
            self::VIEW_TITLE,
            $this->changeColumns,
            $this->filters
        );
    }

    /**
     * Testing return value of getDefaultViewConfig method
     */
    public function testGetDefaultViewConfigResult()
    {
        $this->assertTrue(is_array($this->bookmarkComponent->getDefaultViewConfig()));
    }
}
