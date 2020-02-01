<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Ui\DataProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Aheadworks\Blog\Ui\DataProvider\CategoryDataProvider;
use Aheadworks\Blog\Model\ResourceModel\Category\Grid\CollectionFactory;
use Aheadworks\Blog\Model\ResourceModel\Category\Grid\Collection;
use Aheadworks\Blog\Model\Category;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Test for \Aheadworks\Blog\Ui\DataProvider\CategoryDataProvider
 */
class CategoryDataProviderTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Category constants defined for test
     */
    const DATA_PROVIDER_NAME = 'category_listing_data_source';
    const PRIMARY_FIELD_NAME = 'category_id';
    const REQUEST_FIELD_NAME = 'category_id';
    const PARENT_REQUEST_FIELD_NAME = 'parent';
    const CATEGORY_ID = 1;
    const PARENT_CATEGORY_ID = 2;
    /**#@-*/

    /**
     * @var array
     */
    private $categoryData = [
        'category_id' => self::CATEGORY_ID,
        'store_ids' => [1, 2]
    ];

    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var DataPersistorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataPersistorMock;

    /**
     * @var Collection
     */
    private $collectionMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->setMethods(['getItems', 'getNewEmptyItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $collectionFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->collectionMock));
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->dataPersistorMock = $this->getMockForAbstractClass(DataPersistorInterface::class);

        $this->dataProvider = $objectManager->getObject(
            CategoryDataProvider::class,
            [
                'name' => self::DATA_PROVIDER_NAME,
                'primaryFieldName' => self::PRIMARY_FIELD_NAME,
                'requestFieldName' => self::REQUEST_FIELD_NAME,
                'collectionFactory' => $collectionFactoryMock,
                'request' => $this->requestMock,
                'dataPersistor' => $this->dataPersistorMock
            ]
        );
    }

    /**
     * Testing of get data from collection
     */
    public function testGetDataFromCollection()
    {
        $categoryMock = $this->getMockBuilder(Category::class)
            ->setMethods(['getId', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(self::CATEGORY_ID));
        $categoryMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($this->categoryData));

        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$categoryMock]));
        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(
                [$this->equalTo(self::REQUEST_FIELD_NAME)],
                [$this->equalTo(self::PARENT_REQUEST_FIELD_NAME)]
            )
            ->willReturnOnConsecutiveCalls(
                $this->returnValue(self::CATEGORY_ID),
                $this->returnValue(self::PARENT_CATEGORY_ID)
            );

        $data = $this->dataProvider->getData();
        $this->assertArrayHasKey(self::CATEGORY_ID, $data);
    }

    /**
     * Testing of get data from DataPersistor
     */
    public function testGetDataFromDataPersistor()
    {
        $this->dataPersistorMock->expects($this->once())
            ->method('get')
            ->with('aw_blog_category')
            ->willReturn($this->categoryData);
        $this->dataPersistorMock->expects($this->once())
            ->method('clear')
            ->with('aw_blog_category')
            ->willReturnSelf();

        $categoryMock = $this->getMockBuilder(Category::class)
            ->setMethods(['getId', 'getData', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(self::CATEGORY_ID));
        $categoryMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($this->categoryData));
        $categoryMock->expects($this->once())
            ->method('setData')
            ->with($this->categoryData)
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('getNewEmptyItem')
            ->will($this->returnValue($categoryMock));

        $data = $this->dataProvider->getData();
        $this->assertArrayHasKey(self::CATEGORY_ID, $data);
    }
}
