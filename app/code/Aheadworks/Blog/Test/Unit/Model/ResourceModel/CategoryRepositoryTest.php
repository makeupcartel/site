<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\ResourceModel\CategoryRepository;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Blog\Model\Category;
use Aheadworks\Blog\Model\CategoryRegistry;
use Aheadworks\Blog\Api\Data\CategorySearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SortOrder;
use Aheadworks\Blog\Model\ResourceModel\Category\Collection;
use Aheadworks\Blog\Model\CategoryFactory;
use Aheadworks\Blog\Api\Data\CategoryInterfaceFactory;
use Aheadworks\Blog\Api\Data\CategorySearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\Search\FilterGroup;

/**
 * Test for \Aheadworks\Blog\Model\ResourceModel\CategoryRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const CATEGORY_ID = 1;
    const FILTER_FIELD = 'name';
    const FILTER_VALUE = 'category';
    const SORT_ORDER_FIELD = 'url_key';
    const SORT_ORDER_DIRECTION = 'asc';
    const PAGE_SIZE = 5;
    const CURRENT_PAGE = 1;
    const COLLECTION_SIZE = 10;
    /**#@-*/

    /**
     * @var CategoryRepository
     */
    private $categoryRepositoryMock;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryModelMock;

    /**
     * @var CategoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryMock;

    /**
     * @var CategoryRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRegistryMock;

    /**
     * @var CategorySearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsMock;

    /**
     * @var SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var SortOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sortOrderMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    /**
     * Dummy data of category instance
     *
     * @var array
     */
    private $categoryData = [
        'name' => 'category',
        'url_key' => 'cat',
        'sort_order' => 0,
        'store_ids' => [1, 2]
    ];

    /**
     * Init mocks for tests
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['load', 'save', 'delete'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryModelMock = $this->getMockBuilder(Category::class)
            ->setMethods(['getId', 'addData', 'getCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::CATEGORY_ID));

        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->setMethods(
                [
                    'addFieldToFilter',
                    'addStoreFilter',
                    'getSize',
                    'addOrder',
                    'setCurPage',
                    'setPageSize',
                    'getIterator'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionMock->expects($this->any())
            ->method('getSize')
            ->will($this->returnValue(self::COLLECTION_SIZE));
        $this->collectionMock->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$this->categoryModelMock])));
        $this->categoryModelMock->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($this->collectionMock));

        $categoryFactoryMock = $this->getMockBuilder(CategoryFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->categoryModelMock));

        $this->categoryMock = $this->getMockForAbstractClass(CategoryInterface::class);
        $categoryDataFactoryMock = $this->getMockBuilder(CategoryInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryDataFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->categoryMock));

        $this->categoryRegistryMock = $this->getMockBuilder(CategoryRegistry::class)
            ->setMethods(['retrieve', 'get', 'push', 'remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchResultsMock = $this->getMockForAbstractClass(CategorySearchResultsInterface::class);
        $this->searchResultsMock->expects($this->any())
            ->method('setSearchCriteria')
            ->will($this->returnSelf());
        $searchResultsFactoryMock = $this->getMockBuilder(CategorySearchResultsInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchResultsFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->searchResultsMock));

        $dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->setMethods(['populateWithArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $dataObjectProcessorMock = $this->getMockBuilder(DataObjectProcessor::class)
            ->setMethods(['buildOutputDataArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $dataObjectProcessorMock->expects($this->any())
            ->method('buildOutputDataArray')
            ->with($this->categoryMock, CategoryInterface::class)
            ->will($this->returnValue($this->categoryData));

        $this->filterMock = $this->getMockBuilder(Filter::class)
            ->setMethods(['getField', 'getValue', 'getConditionType'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterMock->expects($this->any())
            ->method('getField')
            ->will($this->returnValue(self::FILTER_FIELD));
        $this->filterMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(self::FILTER_VALUE));
        $this->filterMock->expects($this->any())
            ->method('getConditionType')
            ->will($this->returnValue('eq'));
        $filterGroupMock = $this->getMockBuilder(FilterGroup::class)
            ->setMethods(['getFilters'])
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroupMock->expects($this->any())
            ->method('getFilters')
            ->will($this->returnValue([$this->filterMock]));

        $this->sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->setMethods(['getField', 'getDirection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderMock->expects($this->any())
            ->method('getField')
            ->will($this->returnValue(self::SORT_ORDER_FIELD));
        $this->sortOrderMock->expects($this->any())
            ->method('getDirection')
            ->will($this->returnValue(self::SORT_ORDER_DIRECTION));

        $this->searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $this->searchCriteriaMock->expects($this->any())
            ->method('getSortOrders')
            ->will($this->returnValue([$this->sortOrderMock]));
        $this->searchCriteriaMock->expects($this->any())
            ->method('getFilterGroups')
            ->will($this->returnValue([$filterGroupMock]));
        $this->searchCriteriaMock->expects($this->any())
            ->method('getCurrentPage')
            ->will($this->returnValue(self::CURRENT_PAGE));
        $this->searchCriteriaMock->expects($this->any())
            ->method('getPageSize')
            ->will($this->returnValue(self::PAGE_SIZE));

        $this->categoryRepositoryMock = $objectManager->getObject(
            CategoryRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'categoryFactory' => $categoryFactoryMock,
                'categoryDataFactory' => $categoryDataFactoryMock,
                'categoryRegistry' => $this->categoryRegistryMock,
                'searchResultsFactory' => $searchResultsFactoryMock,
                'dataObjectHelper' => $dataObjectHelperMock,
                'dataObjectProcessor' => $dataObjectProcessorMock
            ]
        );
    }

    /**
     * Test of saving of an instance
     *
     * @dataProvider saveDataProvider
     */
    public function testSave($categoryId)
    {
        $this->categoryMock->expects($this->any())
            ->method('getId')
            ->willReturn($categoryId);
        if ($categoryId) {
            $this->entityManagerMock->expects($this->once())
                ->method('load')
                ->with($this->categoryModelMock, $categoryId);
        } else {
            $this->entityManagerMock->expects($this->never())
                ->method('load')
                ->with($this->categoryModelMock);
        }
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($this->categoryModelMock);
        $this->categoryRegistryMock->expects($this->once())
            ->method('push')
            ->with($this->categoryMock);

        $this->assertSame($this->categoryMock, $this->categoryRepositoryMock->save($this->categoryMock));
    }

    /**
     * Test of retrieving an instance
     */
    public function testGet()
    {
        $this->categoryRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(self::CATEGORY_ID)
            ->willReturn($this->categoryMock);
        $this->assertSame($this->categoryMock, $this->categoryRepositoryMock->get(self::CATEGORY_ID));
    }

    /**
     * Test of retrieving category list
     */
    public function testGetList()
    {
        $this->collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with([self::FILTER_FIELD], [['eq' => self::FILTER_VALUE]]);
        $this->collectionMock->expects($this->exactly(1))
            ->method('addOrder')
            ->with(self::SORT_ORDER_FIELD, self::SORT_ORDER_DIRECTION);
        $this->collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with(self::PAGE_SIZE)
            ->will($this->returnSelf());
        $this->collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with(self::CURRENT_PAGE)
            ->will($this->returnSelf());
        $this->searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with(self::COLLECTION_SIZE);
        $this->searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$this->categoryMock]);
        $this->assertSame($this->searchResultsMock, $this->categoryRepositoryMock->getList($this->searchCriteriaMock));
    }

    /**
     * Test of delete instance
     */
    public function testDelete()
    {
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->categoryModelMock, self::CATEGORY_ID);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($this->categoryModelMock);
        $this->categoryRegistryMock->expects($this->once())
            ->method('remove')
            ->with(self::CATEGORY_ID);
        $this->categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn(self::CATEGORY_ID);
        $this->categoryRepositoryMock->delete($this->categoryMock);
    }

    /**
     * Test of delete instance by ID
     */
    public function testDeleteById()
    {
        $this->entityManagerMock->expects($this->once())
            ->method('load')
            ->with($this->categoryModelMock, self::CATEGORY_ID);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($this->categoryModelMock);
        $this->categoryRegistryMock->expects($this->once())
            ->method('remove')
            ->with(self::CATEGORY_ID);
        $this->categoryRepositoryMock->deleteById(self::CATEGORY_ID);
    }

    /**
     * Data provider for testSave method
     *
     * @return array
     */
    public function saveDataProvider()
    {
        return [[null], [self::CATEGORY_ID]];
    }
}
