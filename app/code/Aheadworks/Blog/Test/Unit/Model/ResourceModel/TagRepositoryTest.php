<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\TagInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\ResourceModel\TagRepository;
use Magento\Framework\EntityManager\EntityManager;
use Aheadworks\Blog\Model\Tag;
use Aheadworks\Blog\Model\TagRegistry;
use Aheadworks\Blog\Api\Data\TagSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SortOrder;
use Aheadworks\Blog\Model\ResourceModel\Tag\Collection;
use Aheadworks\Blog\Model\TagFactory;
use Aheadworks\Blog\Api\Data\TagInterfaceFactory;
use Aheadworks\Blog\Api\Data\TagSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\Search\FilterGroup;

/**
 * Test for \Aheadworks\Blog\Model\ResourceModel\TagRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TagRepositoryTest extends \PHPUnit\Framework\TestCase
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
     * @var TagRepository
     */
    private $tagRepositoryMock;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityManagerMock;

    /**
     * @var Tag|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagModelMock;

    /**
     * @var TagInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagMock;

    /**
     * @var TagRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagRegistryMock;

    /**
     * @var TagSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * Dummy data of tag instance
     *
     * @var array
     */
    private $tagData = ['name' => 'tag'];

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

        $this->tagModelMock = $this->getMockBuilder(Tag::class)
            ->setMethods(['getId', 'addData', 'getCollection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->tagModelMock->expects($this->any())
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
            ->will($this->returnValue(new \ArrayIterator([$this->tagModelMock])));
        $this->tagModelMock->expects($this->any())
            ->method('getCollection')
            ->will($this->returnValue($this->collectionMock));

        $tagFactoryMock = $this->getMockBuilder(TagFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $tagFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->tagModelMock));

        $this->tagMock = $this->getMockForAbstractClass(TagInterface::class);
        $tagDataFactoryMock = $this->getMockBuilder(TagInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $tagDataFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->tagMock));

        $this->tagRegistryMock = $this->getMockBuilder(TagRegistry::class)
            ->setMethods(['retrieve', 'get', 'push', 'remove'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchResultsMock = $this->getMockForAbstractClass(TagSearchResultsInterface::class);
        $this->searchResultsMock->expects($this->any())
            ->method('setSearchCriteria')
            ->will($this->returnSelf());
        $searchResultsFactoryMock = $this->getMockBuilder(TagSearchResultsInterfaceFactory::class)
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
            ->with($this->tagMock, TagInterface::class)
            ->will($this->returnValue($this->tagData));

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

        $this->tagRepositoryMock = $objectManager->getObject(
            TagRepository::class,
            [
                'entityManager' => $this->entityManagerMock,
                'tagFactory' => $tagFactoryMock,
                'tagDataFactory' => $tagDataFactoryMock,
                'tagRegistry' => $this->tagRegistryMock,
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
    public function testSave($tagId)
    {
        $this->tagMock->expects($this->any())
            ->method('getId')
            ->willReturn($tagId);
        if ($tagId) {
            $this->entityManagerMock->expects($this->once())
                ->method('load')
                ->with($this->tagModelMock, $tagId);
        } else {
            $this->entityManagerMock->expects($this->never())
                ->method('load')
                ->with($this->tagModelMock);
        }
        $this->entityManagerMock->expects($this->once())
            ->method('save')
            ->with($this->tagModelMock);
        $this->tagRegistryMock->expects($this->once())
            ->method('push')
            ->with($this->tagMock);

        $this->assertSame($this->tagMock, $this->tagRepositoryMock->save($this->tagMock));
    }

    /**
     * Test of retrieving an instance
     */
    public function testGet()
    {
        $this->tagRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(self::TAG_ID)
            ->willReturn($this->tagMock);
        $this->assertSame($this->tagMock, $this->tagRepositoryMock->get(self::TAG_ID));
    }

    /**
     * Test of retrieving tag list
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
            ->with([$this->tagMock]);
        $this->assertSame($this->searchResultsMock, $this->tagRepositoryMock->getList($this->searchCriteriaMock));
    }

    /**
     * Test of delete instance
     */
    public function testDelete()
    {
        $this->tagMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::TAG_ID);
        $this->tagRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(self::TAG_ID)
            ->willReturn($this->tagMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($this->tagMock);
        $this->tagRegistryMock->expects($this->once())
            ->method('remove')
            ->with(self::TAG_ID);
        $this->tagRepositoryMock->delete($this->tagMock);
    }

    /**
     * Test of delete instance by ID
     */
    public function testDeleteById()
    {
        $this->tagRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(self::TAG_ID)
            ->willReturn($this->tagMock);
        $this->entityManagerMock->expects($this->once())
            ->method('delete')
            ->with($this->tagMock);
        $this->tagRegistryMock->expects($this->once())
            ->method('remove')
            ->with(self::TAG_ID);
        $this->tagRepositoryMock->deleteById(self::TAG_ID);
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
