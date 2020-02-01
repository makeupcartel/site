<?php
/**
 * Copyright 2018 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\PostList;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Block\PostList\Pager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Api\Data\PostSearchResultsInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Test for \Aheadworks\Blog\Block\PostList\Pager
 */
class PagerTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Pager constants defined for test
     */
    const PAGE_VAR_NAME = 'p';
    const LIMIT_VAR_NAME = 'limit';
    const PAGE = 2;
    const LIMIT = 10;
    const TOTAL_COUNT = 30;
    /**#@-*/

    /**
     * @var Pager
     */
    private $pager;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var PostRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $repositoryMock;

    /**
     * @var PostSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['setCurrentPage', 'setPageSize', 'create'])
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('setCurrentPage')
            ->will($this->returnSelf());
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('setPageSize')
            ->will($this->returnSelf());
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->searchCriteriaMock));

        $this->searchResultsMock = $this->getMockForAbstractClass(PostSearchResultsInterface::class);
        $this->searchResultsMock->expects($this->any())
            ->method('getTotalCount')
            ->will($this->returnValue(self::TOTAL_COUNT));
        $this->repositoryMock = $this->getMockForAbstractClass(PostRepositoryInterface::class);
        $this->repositoryMock->expects($this->any())
            ->method('getList')
            ->with($this->equalTo($this->searchCriteriaMock))
            ->will($this->returnValue($this->searchResultsMock));

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );

        $this->pager = $objectManager->getObject(
            Pager::class,
            ['context' => $context]
        );
        $this->pager->setPageVarName(self::PAGE_VAR_NAME);
        $this->pager->setLimitVarName(self::LIMIT_VAR_NAME);
    }

    /**
     * Prepare request mock
     *
     * @param int $currentPage
     * @param int $limit
     * @return void
     */
    private function prepareRequestMock($currentPage = self::PAGE, $limit = self::LIMIT)
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->will(
                $this->returnValueMap(
                    [
                        [self::PAGE_VAR_NAME, 1, $currentPage],
                        [self::LIMIT_VAR_NAME, null, $limit]
                    ]
                )
            );
    }

    /**
     * Testing of applying pagination
     */
    public function testApplyPagination()
    {
        $this->prepareRequestMock();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setCurrentPage')
            ->with($this->equalTo(self::PAGE));
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setPageSize')
            ->with($this->equalTo(self::LIMIT));
        $this->pager->applyPagination($this->searchCriteriaBuilderMock, $this->repositoryMock);
        $this->assertSame($this->searchResultsMock, $this->pager->getResultItems());
    }

    /**
     * Testing of default return value of getResultItems method
     */
    public function testGetResultItems()
    {
        $this->assertEmpty($this->pager->getResultItems());
    }

    /**
     * Testing of retrieving of the current page
     */
    public function testGetCurrentPage()
    {
        $this->prepareRequestMock();
        $this->assertEquals(self::PAGE, $this->pager->getCurrentPage());
    }

    /**
     * Testing of retrieving of the current page with displacement
     *
     * @dataProvider getCurPageWithDisplacementDataProvider
     */
    public function testGetCurPageWithDisplacement($displacement, $expectedResult)
    {
        $this->prepareRequestMock();
        $this->pager->applyPagination($this->searchCriteriaBuilderMock, $this->repositoryMock);
        $this->assertEquals($expectedResult, $this->pager->getCurPageWithDisplacement($displacement));
    }

    /**
     * Testing of isFirstPage method
     *
     * @dataProvider isFirstPageDataProvider
     */
    public function testIsFirstPage($page, $expectedResult)
    {
        $this->prepareRequestMock($page);
        $this->assertEquals($expectedResult, $this->pager->isFirstPage());
    }

    /**
     * Testing of isLastPage method
     *
     * @dataProvider isLastPageDataProvider
     */
    public function testIsLastPage($page, $expectedResult)
    {
        $this->prepareRequestMock($page);
        $this->pager->applyPagination($this->searchCriteriaBuilderMock, $this->repositoryMock);
        $this->assertEquals($expectedResult, $this->pager->isLastPage());
    }

    /**
     * Testing of retrieving of the last page number
     */
    public function testGetLastPageNum()
    {
        $this->prepareRequestMock();
        $this->pager->applyPagination($this->searchCriteriaBuilderMock, $this->repositoryMock);
        $this->assertEquals(3, $this->pager->getLastPageNum());
    }

    /**
     * Testing of retrieving of the first page url
     */
    public function testGetFirstPageUrl()
    {
        $firstPageUrl = 'http://localhost/blog?p=1';
        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with(
                $this->anything(),
                $this->callback(
                    function ($params) {
                        return $params['_query'] == [self::PAGE_VAR_NAME => 1];
                    }
                )
            )
            ->willReturn($firstPageUrl);
        $this->assertEquals($firstPageUrl, $this->pager->getFirstPageUrl());
    }

    /**
     * Testing of retrieving of previous page url
     */
    public function testGetPreviousPageUrl()
    {
        $previousPageUrl = 'http://localhost/blog?p=1';
        $this->prepareRequestMock();
        $this->pager->applyPagination($this->searchCriteriaBuilderMock, $this->repositoryMock);
        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with(
                $this->anything(),
                $this->callback(
                    function ($params) {
                        return $params['_query'] == [self::PAGE_VAR_NAME => 1];
                    }
                )
            )
            ->willReturn($previousPageUrl);
        $this->assertEquals($previousPageUrl, $this->pager->getPreviousPageUrl());
    }

    /**
     * Testing of retrieving of next page url
     */
    public function testGetNextPageUrl()
    {
        $nextPageUrl = 'http://localhost/blog?p=3';
        $this->prepareRequestMock();
        $this->pager->applyPagination($this->searchCriteriaBuilderMock, $this->repositoryMock);
        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with(
                $this->anything(),
                $this->callback(
                    function ($params) {
                        return $params['_query'] == [self::PAGE_VAR_NAME => 3];
                    }
                )
            )
            ->willReturn($nextPageUrl);
        $this->assertEquals($nextPageUrl, $this->pager->getNextPageUrl());
    }

    /**
     * Testing of retrieving of the last page url
     */
    public function testGetLastPageUrl()
    {
        $lastPageUrl = 'http://localhost/blog?p=3';
        $this->prepareRequestMock();
        $this->pager->applyPagination($this->searchCriteriaBuilderMock, $this->repositoryMock);
        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with(
                $this->anything(),
                $this->callback(
                    function ($params) {
                        return $params['_query'] == [self::PAGE_VAR_NAME => 3];
                    }
                )
            )
            ->willReturn($lastPageUrl);
        $this->assertEquals($lastPageUrl, $this->pager->getLastPageUrl());
    }

    /**
     * Testing of getPagerUrl method
     */
    public function testGetPagerUrl()
    {
        $path = '*/*/*';
        $query = ['paramName' => 'paramValue'];
        $pageUrl = 'http://localhost/blog?paramName=paramValue';
        $this->urlBuilderMock->expects($this->atLeastOnce())
            ->method('getUrl')
            ->with(
                $this->equalTo(null),
                $this->callback(
                    function ($params) use ($path, $query) {
                        return isset($params['_query']) && $params['_query'] == $query
                            && isset($params['_direct']) && $params['_direct'] == $path;
                    }
                )
            )
            ->willReturn($pageUrl);
        $this->assertEquals($pageUrl, $this->pager->getPagerUrl($query));
    }

    /**
     * @return array
     */
    public function getCurPageWithDisplacementDataProvider()
    {
        return [[-2, 1], [-1, 1], [0, 2], [1, 3], [2, 3]];
    }

    /**
     * @return array
     */
    public function isFirstPageDataProvider()
    {
        return [[1, true], [2, false]];
    }

    /**
     * @return array
     */
    public function isLastPageDataProvider()
    {
        return [[2, false], [3, true]];
    }
}
