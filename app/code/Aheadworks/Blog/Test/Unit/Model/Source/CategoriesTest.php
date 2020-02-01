<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\ResourceModel\Category\Collection;
use Aheadworks\Blog\Model\Source\Categories;
use Aheadworks\Blog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Test for \Aheadworks\Blog\Model\Source\Categories
 */
class CategoriesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Categories
     */
    private $categoriesSourceModel;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryCollection;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->categoryCollection = $this->getMockBuilder(Collection::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->categoryCollection));
        $this->categoriesSourceModel = $objectManager->getObject(
            Categories::class,
            ['categoryCollectionFactory' => $categoryCollectionFactoryMock]
        );
    }

    /**
     * Testing 'toOptionArray' method call
     */
    public function testToOptionArray()
    {
        $optionArray = [['option value' => 'option label']];
        $this->categoryCollection->expects($this->atLeastOnce())
            ->method('toOptionArray')
            ->willReturn($optionArray);
        $this->assertEquals($optionArray, $this->categoriesSourceModel->toOptionArray());
    }
}
