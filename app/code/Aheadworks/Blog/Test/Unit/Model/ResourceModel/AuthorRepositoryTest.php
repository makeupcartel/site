<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\ResourceModel\AuthorRepository;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Blog\Model\Author;
use Aheadworks\Blog\Model\AuthorRegistry;
use Aheadworks\Blog\Api\Data\AuthorSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SortOrder;
use Aheadworks\Blog\Model\ResourceModel\Author\Collection;
use Aheadworks\Blog\Model\AuthorFactory;
use Aheadworks\Blog\Api\Data\AuthorInterfaceFactory;
use Aheadworks\Blog\Api\Data\AuthorSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\Search\FilterGroup;

/**
 * Test for \Aheadworks\Blog\Model\ResourceModel\AuthorRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthorRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const TAG_ID = 1;
    const FILTER_FIELD = 'created_at';
    const FILTER_VALUE = '2016-03-23';
    const SORT_ORDER_FIELD = 'name';
    const SORT_ORDER_DIRECTION = 'asc';
    const PAGE_SIZE = 5;
    const CURRENT_PAGE = 1;
    const COLLECTION_SIZE = 10;
    /**#@-*/

    /**
     * @var AuthorRepository
     */
    private $authorRepositoryMock;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var Author|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorModelMock;

    /**
     * @var AuthorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorMock;

    /**
     * @var AuthorRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorRegistryMock;

    /**
     * @var AuthorSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * Dummy data of author instance
     *
     * @var array
     */
    private $authorData = ['name' => 'author'];

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

        $this->authorModelMock = $this->getMockBuilder(Author::class)
            ->setMethods(['getId', 'addData', 'getCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorModelMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::TAG_ID));

        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->setMethods(['addFieldToFilter', 'getSize', 'addOrder', 'setCurPage', 'setPageSize', 'getIterator'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionMock->expects($this->any())
            ->method('getSize')
            ->will($this->returnValue(self::COLLECTION_SIZE));
        $this->collectionMock->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$this->authorModelMock])));
        $this->authorModelMock->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($this->collectionMock));

        $authorFactoryMock = $this->getMockBuilder(AuthorFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $authorFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->authorModelMock));

        $this->authorMock = $this->getMockForAbstractClass(AuthorInterface::class);
        $authorDataFactoryMock = $this->getMockBuilder(AuthorInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $authorDataFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->authorMock));

        $this->authorRegistryMock = $this->getMockBuilder(AuthorRegistry::class)
            ->setMethods(['retrieve', 'get', 'push', 'remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchResultsMock = $this->getMockForAbstractClass(AuthorSearchResultsInterface::class);
        $this->searchResultsMock->expects($this->any())
            ->method('setSearchCriteria')
            ->will($this->returnSelf());
        $searchResultsFactoryMock = $this->getMockBuilder(AuthorSearchResultsInterfaceFactory::class)
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
            ->with($this->authorMock, AuthorInterface::class)
            ->will($this->returnValue($this->authorData));

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

        $this->authorRepositoryMock = $objectManager->getObject(
            AuthorRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'authorFactory' => $authorFactoryMock,
                'authorDataFactory' => $authorDataFactoryMock,
                'authorRegistry' => $this->authorRegistryMock,
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
    public function testSave($authorId)
    {
        $this->authorMock->expects($this->any())
            ->method('getId')
            ->willReturn($authorId);
        if ($authorId) {
            $this->entityManagerMock->expects($this->once())
                ->method('load')
                ->with($this->authorModelMock, $authorId);
        } else {
            $this->entityManagerMock->expects($this->never())
                ->method('load')
                ->with($this->authorModelMock);
        }
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($this->authorModelMock);
        $this->authorRegistryMock->expects($this->once())
            ->method('push')
            ->with($this->authorMock);

        $this->assertSame($this->authorMock, $this->authorRepositoryMock->save($this->authorMock));
    }

    /**
     * Test of retrieving an instance
     */
    public function testGet()
    {
        $this->authorRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(self::TAG_ID)
            ->willReturn($this->authorMock);
        $this->assertSame($this->authorMock, $this->authorRepositoryMock->get(self::TAG_ID));
    }

    /**
     * Test of retrieving author list
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
            ->with([$this->authorMock]);
        $this->assertSame($this->searchResultsMock, $this->authorRepositoryMock->getList($this->searchCriteriaMock));
    }

    /**
     * Test of delete instance
     */
    public function testDelete()
    {
        $this->authorMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::TAG_ID);
        $this->authorRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(self::TAG_ID)
            ->willReturn($this->authorMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($this->authorMock);
        $this->authorRegistryMock->expects($this->once())
            ->method('remove')
            ->with(self::TAG_ID);
        $this->authorRepositoryMock->delete($this->authorMock);
    }

    /**
     * Test of delete instance by ID
     */
    public function testDeleteById()
    {
        $this->authorRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(self::TAG_ID)
            ->willReturn($this->authorMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($this->authorMock);
        $this->authorRegistryMock->expects($this->once())
            ->method('remove')
            ->with(self::TAG_ID);
        $this->authorRepositoryMock->deleteById(self::TAG_ID);
    }

    /**
     * Data provider for testSave method
     *
     * @return array
     */
    public function saveDataProvider()
    {
        return [[null], [self::TAG_ID]];
    }
}
