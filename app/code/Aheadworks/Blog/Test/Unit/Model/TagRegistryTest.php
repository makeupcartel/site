<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model;

use Aheadworks\Blog\Api\Data\TagInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\TagRegistry;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Blog\Api\Data\TagInterfaceFactory;

/**
 * Test for \Aheadworks\Blog\Model\TagRegistry
 */
class TagRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var int
     */
    const TAG_ID = 1;

    /**
     * @var TagRegistry
     */
    private $tagRegistry;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var TagInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->tagMock = $this->getMockForAbstractClass(TagInterface::class);
        $tagDataFactoryMock = $this->getMockBuilder(TagInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $tagDataFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->tagMock));

        $this->tagRegistry = $objectManager->getObject(
            TagRegistry::class,
            [
                'entityManager' => $this->entityManagerMock,
                'tagDataFactory' => $tagDataFactoryMock
            ]
        );
    }

    /**
     * Testing of retrieving of an instance
     */
    public function testRetrieve()
    {
        $this->entityManagerMock->expects($this->atLeastOnce())
            ->method('load')
            ->with($this->tagMock, self::TAG_ID)
            ->willReturnSelf();
        $this->tagMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::TAG_ID));
        $this->assertSame($this->tagMock, $this->tagRegistry->retrieve(self::TAG_ID));
    }

    /**
     * Testing exception while retrieving of non-existent instance
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRetrieveException()
    {
        $this->tagMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->assertSame($this->tagMock, $this->tagRegistry->retrieve(self::TAG_ID));
    }

    /**
     * Testing that an instance is cached during retrieving
     */
    public function testRetrieveCaching()
    {
        $this->tagMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::TAG_ID));
        $this->tagRegistry->retrieve(self::TAG_ID);
        $this->entityManagerMock->expects($this->never())
            ->method('load')
            ->with($this->tagMock, self::TAG_ID);
        $this->tagRegistry->retrieve(self::TAG_ID);
    }

    /**
     * Testing remove an instance
     */
    public function testRemove()
    {
        $this->tagMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::TAG_ID));
        $this->entityManagerMock->expects($this->exactly(2))
            ->method('load')
            ->with($this->tagMock, self::TAG_ID);
        $this->tagRegistry->retrieve(self::TAG_ID);
        $this->tagRegistry->remove(self::TAG_ID);
        $this->tagRegistry->retrieve(self::TAG_ID);
    }

    /**
     * Test push
     */
    public function testPush()
    {
        $this->tagMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::TAG_ID));
        $this->tagRegistry->retrieve(self::TAG_ID);
        /** @var TagInterface|\PHPUnit_Framework_MockObject_MockObject $newTag */
        $newTag = $this->getMockForAbstractClass(TagInterface::class);
        $newTag->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::TAG_ID));
        $this->tagRegistry->push($newTag);
        $this->assertSame($newTag, $this->tagRegistry->retrieve(self::TAG_ID));
    }
}
