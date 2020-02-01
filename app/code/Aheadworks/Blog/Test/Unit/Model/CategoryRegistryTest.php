<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\CategoryRegistry;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Blog\Api\Data\CategoryInterfaceFactory;

/**
 * Test for \Aheadworks\Blog\Model\CategoryRegistry
 */
class CategoryRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var int
     */
    const CATEGORY_ID = 1;

    /**
     * @var CategoryRegistry
     */
    private $categoryRegistryMock;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var CategoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryMock;

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

        $this->categoryMock = $this->getMockForAbstractClass(CategoryInterface::class);
        $categoryDataFactoryMock = $this->getMockBuilder(CategoryInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryDataFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->categoryMock));

        $this->categoryRegistryMock = $objectManager->getObject(
            CategoryRegistry::class,
            [
                'entityManager' => $this->entityManagerMock,
                'categoryDataFactory' => $categoryDataFactoryMock
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
            ->with($this->categoryMock, self::CATEGORY_ID)
            ->willReturnSelf();
        $this->categoryMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CATEGORY_ID));
        $this->assertSame($this->categoryMock, $this->categoryRegistryMock->retrieve(self::CATEGORY_ID));
    }

    /**
     * Testing exception while retrieving of non-existent instance
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testRetrieveException()
    {
        $this->categoryMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->assertSame($this->categoryMock, $this->categoryRegistryMock->retrieve(self::CATEGORY_ID));
    }

    /**
     * Testing that an instance is cached during retrieving
     */
    public function testRetrieveCaching()
    {
        $this->categoryMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CATEGORY_ID));
        $this->categoryRegistryMock->retrieve(self::CATEGORY_ID);
        $this->entityManagerMock->expects($this->never())
            ->method('load')
            ->with($this->categoryMock, self::CATEGORY_ID);
        $this->categoryRegistryMock->retrieve(self::CATEGORY_ID);
    }

    /**
     * Testing remove an instance
     */
    public function testRemove()
    {
        $this->categoryMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CATEGORY_ID));
        $this->entityManagerMock->expects($this->exactly(2))
            ->method('load')
            ->with($this->categoryMock, self::CATEGORY_ID);
        $this->categoryRegistryMock->retrieve(self::CATEGORY_ID);
        $this->categoryRegistryMock->remove(self::CATEGORY_ID);
        $this->categoryRegistryMock->retrieve(self::CATEGORY_ID);
    }

    /**
     * Test push
     */
    public function testPush()
    {
        $this->categoryMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CATEGORY_ID));
        $this->categoryRegistryMock->retrieve(self::CATEGORY_ID);
        /** @var CategoryInterface|\PHPUnit_Framework_MockObject_MockObject $newCategory */
        $newCategory = $this->getMockForAbstractClass(CategoryInterface::class);
        $newCategory->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CATEGORY_ID));
        $this->categoryRegistryMock->push($newCategory);
        $this->assertSame($newCategory, $this->categoryRegistryMock->retrieve(self::CATEGORY_ID));
    }
}
