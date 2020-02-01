<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\Source\Tags;
use Aheadworks\Blog\Model\ResourceModel\Tag\Collection;
use Aheadworks\Blog\Model\ResourceModel\Tag\CollectionFactory;

/**
 * Test for \Aheadworks\Blog\Model\Source\Tags
 */
class TagsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Tags
     */
    private $tagsSourceModel;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagsCollection;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->tagsCollection = $this->getMockBuilder(Collection::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $tagsCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $tagsCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->tagsCollection));
        $this->tagsSourceModel = $objectManager->getObject(
            Tags::class,
            ['tagCollectionFactory' => $tagsCollectionFactoryMock]
        );
    }

    /**
     * Testing 'toOptionArray' method call
     */
    public function testToOptionArray()
    {
        $optionArray = [['option value' => 'option label']];
        $this->tagsCollection->expects($this->atLeastOnce())
            ->method('toOptionArray')
            ->willReturn($optionArray);
        $this->assertEquals($optionArray, $this->tagsSourceModel->toOptionArray());
    }
}
