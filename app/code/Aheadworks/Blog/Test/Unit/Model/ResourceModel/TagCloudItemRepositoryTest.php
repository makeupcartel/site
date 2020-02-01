<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\ResourceModel;

use Aheadworks\Blog\Api\Data\TagInterface;
use Aheadworks\Blog\Api\Data\TagCloudItemInterface;
use Aheadworks\Blog\Api\Data\TagCloudItemSearchResultsInterface;
use Aheadworks\Blog\Model\ResourceModel\TagCloudItemRepository;
use Aheadworks\Blog\Model\ResourceModel\TagCloudItem\Collection as TagCloudItemCollection;
use Aheadworks\Blog\Model\TagRegistry;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\Data\TagCloudItemFactory;
use Aheadworks\Blog\Model\Data\TagFactory;
use Aheadworks\Blog\Model\ResourceModel\TagCloudItem;
use Aheadworks\Blog\Model\Tag;
use Aheadworks\Blog\Model\ResourceModel\TagCloudItem\CollectionFactory;

/**
 * Test for \Aheadworks\Blog\Model\ResourceModel\TagCloudItemRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TagCloudItemRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const TAG_ID = 1;
    const STORE_ID = 2;
    const FILTER_FIELD = 'post_count';
    const FILTER_VALUE = '10';
    const SORT_ORDER_FIELD = 'post_count';
    const SORT_ORDER_DIRECTION = 'desc';
    const PAGE_SIZE = 5;
    const CURRENT_PAGE = 1;
    const COLLECTION_SIZE = 10;
    const POST_COUNT = 10;
    const MIN_POST_COUNT = 7;
    const MAX_POST_COUNT = 20;
    /**#@-*/

    /**
     * @var TagCloudItemRepository
     */
    private $tagCloudItemRepository;

    /**
     * @var TagCloudItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagCloudItemMock;

    /**
     * @var TagInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagMock;

    /**
     * @var TagRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagRegistryMock;

    /**
     * @var TagCloudItemCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionMock;

    /**
     * @var TagCloudItemSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->tagCloudItemMock = $this->getMockForAbstractClass(TagCloudItemInterface::class);
        $tagCloudItemFactoryMock = $this->getMockBuilder(TagCloudItemFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $tagCloudItemFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->tagCloudItemMock));

        $this->tagMock = $this->getMockForAbstractClass(TagInterface::class);
        $tagDataFactoryMock = $this->getMockBuilder(TagFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $tagDataFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->tagMock));

        $this->tagRegistryMock = $this->getMockBuilder(TagRegistry::class)
            ->setMethods(['retrieve'])
            ->disableOriginalConstructor()
            ->getMock();

        $tagCloudItemResourceModelMock = $this->getMockBuilder(TagCloudItem::class)
            ->setMethods(['getPostCount'])
            ->disableOriginalConstructor()
            ->getMock();
        $tagCloudItemResourceModelMock->expects($this->any())
            ->method('getPostCount')
            ->with(self::TAG_ID, self::STORE_ID)
            ->will($this->returnValue(self::POST_COUNT));

        $tagModel = $this->getMockBuilder(Tag::class)
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMock();
        $tagModel->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(['name' => 'tag', 'post_count' => 10]));
        $this->collectionMock = $this->getMockBuilder(TagCloudItemCollection::class)
            ->setMethods(
                [
                    'joinPostCount',
                    'addCategoryFilter',
                    'addFieldToFilter',
                    'getSize',
                    'addOrder',
                    'setCurPage',
                    'setPageSize',
                    'getMaxPostCount',
                    'getMinPostCount',
                    'getIterator'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->collectionMock->expects($this->any())
            ->method('getSize')
            ->will($this->returnValue(self::COLLECTION_SIZE));
        $this->collectionMock->expects($this->any())
            ->method('getMaxPostCount')
            ->will($this->returnValue(self::MAX_POST_COUNT));
        $this->collectionMock->expects($this->any())
            ->method('getMinPostCount')
            ->will($this->returnValue(self::MIN_POST_COUNT));
        $this->collectionMock->expects($this->any())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator([$tagModel])));

        $tagCloudItemCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $tagCloudItemCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->collectionMock));

        $this->searchResultsMock = $this->getMockForAbstractClass(TagCloudItemSearchResultsInterface::class);
        $this->searchResultsMock->expects($this->any())
            ->method('setSearchCriteria')
            ->will($this->returnSelf());

        $searchResultsFactoryMock = $this->getMockBuilder(
            \Aheadworks\Blog\Api\Data\TagCloudItemSearchResultsInterfaceFactory::class
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchResultsFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->searchResultsMock));

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

        $this->tagCloudItemRepository = $objectManager->getObject(
            TagCloudItemRepository::class,
            [
                'tagCloudItemFactory' => $tagCloudItemFactoryMock,
                'tagDataFactory' => $tagDataFactoryMock,
                'tagRegistry' => $this->tagRegistryMock,
                'tagCloudItemResourceModel' => $tagCloudItemResourceModelMock,
                'tagCloudItemCollectionFactory' => $tagCloudItemCollectionFactoryMock,
                'searchResultsFactory' => $searchResultsFactoryMock
            ]
        );
    }

    /**
     * Test retrieve of a tag cloud item instance
     */
    public function testGet()
    {
        $this->tagRegistryMock->expects($this->once())
            ->method('retrieve')
            ->with(self::TAG_ID)
            ->willReturn($this->tagMock);
        $this->tagCloudItemMock->expects($this->once())
            ->method('setTag')
            ->with($this->tagMock)
            ->willReturnSelf();
        $this->tagCloudItemMock->expects($this->once())
            ->method('setPostCount')
            ->with(self::POST_COUNT)
            ->willReturnSelf();
        $this->assertSame($this->tagCloudItemMock, $this->tagCloudItemRepository->get(self::TAG_ID, self::STORE_ID));
    }

    /**
     * Test of retrieving tag cloud item list
     */
    public function testGetList()
    {
        $this->collectionMock->expects($this->once())
            ->method('joinPostCount')
            ->with(self::STORE_ID);
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
            ->with([$this->tagCloudItemMock]);
        $this->searchResultsMock->expects($this->once())
            ->method('setMaxPostCount')
            ->with(self::MAX_POST_COUNT);
        $this->searchResultsMock->expects($this->once())
            ->method('setMinPostCount')
            ->with(self::MIN_POST_COUNT);
        $this->assertSame(
            $this->searchResultsMock,
            $this->tagCloudItemRepository->getList($this->searchCriteriaMock, self::STORE_ID)
        );
    }
}
