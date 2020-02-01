<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Sidebar;

use Aheadworks\Blog\Block\Sidebar\Category;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Blog\Model\Config;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Aheadworks\Blog\Api\Data\CategorySearchResultsInterface;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;

/**
 * Test for \Aheadworks\Blog\Block\Sidebar\Category
 */
class CategoryTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const STORE_ID = 1;
    const CATEGORY_LIMIT_CONFIG_VALUE = 5;
    /**#@-*/

    /**
     * @var Category
     */
    private $block;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepositoryMock;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerMock;

    /**
     * @var Config
     */
    private $configMock;

    /**
     * Init mocks for tests
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::STORE_ID));
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));
        $context = $objectManager->getObject(
            Context::class,
            [
                'storeManager' => $this->storeManagerMock
            ]
        );

        $this->categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'addSortOrder', 'create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getNumCategoriesToDisplay'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock->expects($this->any())
            ->method('getNumCategoriesToDisplay')
            ->will($this->returnValue(self::CATEGORY_LIMIT_CONFIG_VALUE));

        $this->block = $objectManager->getObject(
            Category::class,
            [
                'context' => $context,
                'categoryRepository' => $this->categoryRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'config' => $this->configMock
            ]
        );
    }

    /**
     * Prepare $this->categoryRepositoryMock for retrieve a given items by getList call
     *
     * @param array $items Return items
     * @param int $calls Number of getList method calls
     * @return void
     */
    private function prepareCategoryRepositoryMock($items = [], $calls = 1)
    {
        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $searchResultMock = $this->getMockForAbstractClass(CategorySearchResultsInterface::class);
        $searchResultMock->expects($this->exactly($calls))
            ->method('getItems')
            ->willReturn($items);

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::STORE_ID));
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));

        $this->searchCriteriaBuilderMock->expects($this->exactly(3 * $calls))
            ->method('addFilter')
            ->withAnyParameters()
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->exactly($calls))
            ->method('addSortOrder')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->exactly($calls))
            ->method('create')
            ->will($this->returnValue($searchCriteriaMock));
        $this->categoryRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->will($this->returnValue($searchResultMock));
    }

    /**
     * Testing of retrieving of child categories
     */
    public function testGetChildCategories()
    {
        $categoryMock = $this->getMockForAbstractClass(CategoryInterface::class);
        $this->prepareCategoryRepositoryMock([$categoryMock]);
        $this->assertEquals([$categoryMock], $this->block->getChildCategories());
    }

    /**
     * Testing of retrieving category limit to display from config
     */
    public function testGetNumCategoriesToDisplay()
    {
        $expectedValue = self::CATEGORY_LIMIT_CONFIG_VALUE;
        $actualValue = $this->configMock->getNumCategoriesToDisplay(self::STORE_ID);
        $this->assertEquals($expectedValue, $actualValue);
    }
}
