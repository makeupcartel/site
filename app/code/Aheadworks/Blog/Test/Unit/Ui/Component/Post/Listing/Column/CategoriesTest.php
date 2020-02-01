<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Ui\Component\Post\Listing\Column;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Ui\Component\Post\Listing\Column\Categories;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Blog\Api\Data\CategorySearchResultsInterface;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;

/**
 * Test for \Aheadworks\Blog\Ui\Component\Post\Listing\Column\Categories
 */
class CategoriesTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Category constants defined for test
     */
    const CATEGORY1_NAME = 'Category 1';
    const CATEGORY2_NAME = 'Category 2';
    const POST_CATEGORY_IDS = [1, 2];
    /**#@-*/

    /**
     * @var Categories
     */
    private $column;

    /**
     * @var CategoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $category1Mock;

    /**
     * @var CategoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $category2Mock;

    /**
     * @var array
     */
    private $post = [
        'category_ids' => self::POST_CATEGORY_IDS
    ];

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $processorMock = $this->getMockBuilder(Processor::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $processorMock->expects($this->any())
            ->method('register');
        $contextMock = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();
        $contextMock->expects($this->any())
            ->method('getProcessor')
            ->willReturn($processorMock);

        $this->category1Mock = $this->getMockForAbstractClass(CategoryInterface::class);
        $this->category1Mock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(self::CATEGORY1_NAME));
        $this->category2Mock = $this->getMockForAbstractClass(CategoryInterface::class);
        $this->category2Mock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(self::CATEGORY2_NAME));

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->will($this->returnSelf());
        $searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($searchCriteriaMock));

        $searchResultsMock = $this->getMockForAbstractClass(CategorySearchResultsInterface::class);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$this->category1Mock, $this->category2Mock]));

        $categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $categoryRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->equalTo($searchCriteriaMock))
            ->will($this->returnValue($searchResultsMock));

        $this->column = $objectManager->getObject(
            Categories::class,
            [
                'context' => $contextMock,
                'categoryRepository' => $categoryRepositoryMock,
                'searchCriteriaBuilder' => $searchCriteriaBuilderMock
            ]
        );
    }

    /**
     * Testing of prepareDataSource method
     */
    public function testPrepareDataSource()
    {
        $dataSource = ['data' => ['items' => [$this->post]]];
        $dataSourcePrepared = $this->column->prepareDataSource($dataSource);
        $postItem = $dataSourcePrepared['data']['items'][0];
        $this->assertArrayHasKey('categories', $postItem);
        $this->assertEquals(self::CATEGORY1_NAME . ', ' . self::CATEGORY2_NAME, $postItem['categories']);
    }
}
