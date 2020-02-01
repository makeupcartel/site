<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Widget;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Block\Widget\TagCloud;
use Magento\Framework\Api\SearchCriteriaInterface;
use Aheadworks\Blog\Api\TagCloudItemRepositoryInterface;
use Aheadworks\Blog\Api\Data\TagCloudItemSearchResultsInterface;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Url;
use Aheadworks\Blog\Api\Data\TagCloudItemInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Blog\Api\Data\TagInterface;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;

/**
 * Test for \Aheadworks\Blog\Block\Widget\TagCloud
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TagCloudTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Pager constants defined for test
     */
    const STORE_ID = 1;
    const CATEGORY_ID = 2;
    const MAX_WEIGHT = 1.5;
    const MIN_WEIGHT = 0.5;
    const SLOPE = 0.1;
    /**#@-*/

    /**
     * @var TagCloud
     */
    private $block;

    /**
     * @var SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var TagCloudItemRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagCloudItemRepositoryMock;

    /**
     * @var TagCloudItemSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var TagCloudItemInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagCloudItemMock;

    /**
     * @var SortOrderBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sortOrderBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('blog_category_id'))
            ->will($this->returnValue(self::CATEGORY_ID));

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::STORE_ID));
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));

        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $requestMock,
                'storeManager' => $storeManagerMock
            ]
        );

        $this->tagCloudItemRepositoryMock = $this->getMockForAbstractClass(TagCloudItemRepositoryInterface::class);
        $this->searchResultsMock = $this->getMockForAbstractClass(TagCloudItemSearchResultsInterface::class);

        $this->sortOrderBuilderMock = $this->getMockBuilder(SortOrderBuilder::class)
            ->setMethods(['setField', 'setDirection', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);

        $sortOrderMock = $this->getMockBuilder(SortOrder::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sortOrderBuilderMock->expects($this->any())
            ->method('setField')
            ->with('post_count')
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->any())
            ->method('setDirection')
            ->with(SortOrder::SORT_DESC)
            ->willReturnSelf();
        $this->sortOrderBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($sortOrderMock);
        $searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['setPageSize', 'addFilter', 'create', 'addSortOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderMock->expects($this->any())
            ->method('addSortOrder')
            ->with($sortOrderMock)
            ->willReturnSelf();
        $searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->searchCriteriaMock));

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getNumPopularTags', 'isBlogEnabled', 'isHighlightTags'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock->expects($this->any())
            ->method('getNumPopularTags')
            ->will($this->returnValue(10));

        $this->urlMock = $this->getMockBuilder(Url::class)
            ->setMethods(['getSearchByTagUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->tagCloudItemMock = $this->getMockForAbstractClass(TagCloudItemInterface::class);

        $this->block = $objectManager->getObject(
            TagCloud::class,
            [
                'context' => $context,
                'tagCloudItemRepository' => $this->tagCloudItemRepositoryMock,
                'searchCriteriaBuilder' => $searchCriteriaBuilderMock,
                'config' => $this->configMock,
                'url' => $this->urlMock,
                'sortOrderBuilder' => $this->sortOrderBuilderMock,
                'data' => [
                    'max_weight' => self::MAX_WEIGHT,
                    'min_weight' => self::MIN_WEIGHT,
                    'slope' => self::SLOPE
                ]
            ]
        );
    }

    /**
     * Testing of retrieving of tag cloud items
     */
    public function testGetItems()
    {
        $items = [$this->tagCloudItemMock];
        $this->searchResultsMock->expects($this->any())
            ->method('getItems')
            ->willReturn($items);
        $this->tagCloudItemRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock, self::STORE_ID)
            ->willReturn($this->searchResultsMock);
        $this->assertEquals($items, $this->block->getItems());
        $this->block->getItems();
    }

    /**
     * Testing of isEnabled method
     *
     * @dataProvider isEnabledDataProvider
     * @param bool $value
     */
    public function testIsEnabled($value)
    {
        $this->configMock->expects($this->any())
            ->method('isBlogEnabled')
            ->willReturn($value);
        $this->assertEquals($value, $this->block->isEnabled());
    }

    /**
     * Testing of isCloudMode method
     *
     * @dataProvider isCloudModeDataProvider
     * @param int $configValue
     * @param bool $expectedResult
     */
    public function testIsCloudMode($configValue, $expectedResult)
    {
        $this->configMock->expects($this->any())
            ->method('isHighlightTags')
            ->willReturn($configValue);
        $this->assertEquals($expectedResult, $this->block->isCloudMode());
    }

    /**
     * Testing of tag weight calculation
     *
     * @dataProvider getWeightDataProvider
     * @param int $postNum
     * @param int $maxPostNum
     * @param int $minPostNum
     * @param int $weight
     */
    public function testGetWeight($postNum, $maxPostNum, $minPostNum, $weight)
    {
        $this->tagCloudItemMock->expects($this->any())
            ->method('getPostCount')
            ->willReturn($postNum);
        $this->searchResultsMock->expects($this->any())
            ->method('getMaxPostCount')
            ->willReturn($maxPostNum);
        $this->searchResultsMock->expects($this->any())
            ->method('getMinPostCount')
            ->willReturn($minPostNum);
        $this->tagCloudItemRepositoryMock->expects($this->any())
            ->method('getList')
            ->with($this->searchCriteriaMock, self::STORE_ID)
            ->willReturn($this->searchResultsMock);
        $this->assertEquals($weight, $this->block->getWeight($this->tagCloudItemMock));
    }

    /**
     * Testing of getSearchByTagUrl method
     */
    public function testGetSearchByTagUrl()
    {
        $url = 'http://localhost/blog/tag/tag';
        /** @var \Aheadworks\Blog\Api\Data\TagInterface|\PHPUnit_Framework_MockObject_MockObject $tag */
        $tag = $this->getMockForAbstractClass(TagInterface::class);
        $this->tagCloudItemMock->expects($this->any())
            ->method('getTag')
            ->willReturn($tag);
        $this->urlMock->expects($this->once())
            ->method('getSearchByTagUrl')
            ->with($tag)
            ->willReturn($url);
        $this->assertEquals($url, $this->block->getSearchByTagUrl($this->tagCloudItemMock));
    }

    /**
     * Data provider for testIsCloudMode method
     *
     * @return array
     */
    public function isCloudModeDataProvider()
    {
        return [[1, true], [0, false]];
    }

    /**
     * Data provider for testIsEnabled method
     *
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * Data provider for testGetWeight method
     *
     * @return array
     */
    public function getWeightDataProvider()
    {
        return [
            [5, 15, 5, 52.0],
            [10, 15, 5, 100.0],
            [15, 15, 5, 148.0]
        ];
    }
}
