<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Observer;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Api\Data\CategorySearchResultsInterface;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Url;
use Aheadworks\Blog\Observer\AddBlogToTopmenuItemsObserver;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Topmenu;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Tree;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Blog\Observer\AddBlogToTopmenuItemsObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddBlogToTopmenuItemsObserverTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Observer constants defined for test
     */
    const BLOG_TITLE_CONFIG_VALUE = 'blog';
    const CATEGORY_ID = 1;
    const CATEGORY_NAME = 'category';
    const STORE_ID = 1;
    const BLOG_HOME_URL = 'http://localhost/blog';
    const CATEGORY_URL = 'http://localhost/blog/cat';
    /**#@-*/

    /**
     * @var AddBlogToTopmenuItemsObserver
     */
    private $observer;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var NodeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nodeFactoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreRegistryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isBlogEnabled', 'getBlogTitle', 'isMenuLinkEnabled'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->nodeFactoryMock = $this->getMockBuilder(NodeFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'addSortOrder', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $this->coreRegistryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->setMethods(['registry'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $urlMock = $this->getMockBuilder(Url::class)
            ->setMethods(['getCategoryUrl', 'getBlogHomeUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $urlMock->expects($this->any())
            ->method('getCategoryUrl')
            ->will($this->returnValue(self::CATEGORY_URL));
        $urlMock->expects($this->any())
            ->method('getBlogHomeUrl')
            ->will($this->returnValue(self::BLOG_HOME_URL));

        $this->observer = $objectManager->getObject(
            AddBlogToTopmenuItemsObserver::class,
            [
                'categoryRepository' => $this->categoryRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'url' => $urlMock,
                'config' => $this->configMock,
                'coreRegistry' => $this->coreRegistryMock,
                'storeManager' => $this->storeManagerMock,
                'request' => $this->requestMock,
                'nodeFactory' => $this->nodeFactoryMock
            ]
        );
    }

    /**
     * Testing of getCategories method
     */
    public function testGetCategories()
    {
        $categoryMock = $this->getMockForAbstractClass(CategoryInterface::class);
        $this->prepareCategoryRepositoryMock([$categoryMock]);

        $class = new \ReflectionClass($this->observer);
        $method = $class->getMethod('getCategories');
        $method->setAccessible(true);
        $this->assertEquals([$categoryMock], $method->invoke($this->observer));
    }

    /**
     * Testing of execute method
     *
     * @dataProvider executeDataProvider
     * @param bool $isNeedToAdd
     * @param CategoryInterface|\PHPUnit_Framework_MockObject_MockObject $category
     */
    public function testExecute($isNeedToAdd, $category = null)
    {
        $treeMock = $this->getMockBuilder(Tree::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $blogNodeMock = $this->getMockBuilder(Node::class)
            ->setMethods(['getTree', 'addChild'])
            ->disableOriginalConstructor()
            ->getMock();
        $blogNodeMock->expects($this->any())
            ->method('getTree')
            ->will($this->returnValue($treeMock));
        $categoryNodeMock = $this->getMockBuilder(Node::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $categoryNodeMock->expects($this->any())
            ->method('getTree')
            ->will($this->returnValue($treeMock));
        $parentNodeMock = $this->getMockBuilder(Node::class)
            ->setMethods(['getTree', 'addChild'])
            ->disableOriginalConstructor()
            ->getMock();
        $parentNodeMock->expects($this->any())
            ->method('getTree')
            ->will($this->returnValue($treeMock));
        $menuBlockMock = $this->getMockBuilder(Topmenu::class)
            ->setMethods(['addIdentity', 'getMenu'])
            ->disableOriginalConstructor()
            ->getMock();
        $menuBlockMock->expects($this->any())
            ->method('getMenu')
            ->will($this->returnValue($parentNodeMock));
        $eventMock = $this->getMockBuilder(Event::class)
            ->setMethods(['getBlock'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventMock->expects($this->any())
            ->method('getBlock')
            ->will($this->returnValue($menuBlockMock));

        /** @var Observer|\PHPUnit_Framework_MockObject_MockObject $observerMock */
        $observerMock = $this->getMockBuilder(Observer::class)
            ->setMethods(['getEvent'])
            ->disableOriginalConstructor()
            ->getMock();
        $observerMock->expects($isNeedToAdd ? $this->once() : $this->never())
            ->method('getEvent')
            ->will($this->returnValue($eventMock));

        $this->configMock->expects($this->any())
            ->method('getBlogTitle')
            ->will($this->returnValue(self::BLOG_TITLE_CONFIG_VALUE));
        $this->configMock->expects($this->once())
            ->method('isBlogEnabled')
            ->willReturn($isNeedToAdd);
        $this->configMock->expects($this->exactly($isNeedToAdd ? 1 : 0))
            ->method('isMenuLinkEnabled')
            ->willReturn($isNeedToAdd);

        if (!$isNeedToAdd) {
            $this->nodeFactoryMock->expects($this->never())
                ->method('create');
            $parentNodeMock->expects($this->never())
                ->method('addChild');
        } else {
            $parentNodeMock->expects($this->once())
                ->method('addChild')
                ->with($blogNodeMock);
            if ($category) {
                $this->prepareCategoryRepositoryMock([$category], 2);
                $this->nodeFactoryMock->expects($this->exactly(2))
                    ->method('create')
                    ->willReturnOnConsecutiveCalls($blogNodeMock, $categoryNodeMock);
                $menuBlockMock->expects($this->exactly(2))
                    ->method('addIdentity')
                    ->withConsecutive(['aw_blog_category'], ['aw_blog_category_' . self::CATEGORY_ID]);
                $blogNodeMock->expects($this->once())
                    ->method('addChild')
                    ->with($categoryNodeMock);
            } else {
                $this->prepareCategoryRepositoryMock([], 2);
                $this->nodeFactoryMock->expects($this->once())
                    ->method('create')
                    ->willReturn($blogNodeMock);
                $menuBlockMock->expects($this->once())
                    ->method('addIdentity')
                    ->with('aw_blog_category');
            }
        }

        $this->observer->execute($observerMock);
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

        $this->searchCriteriaBuilderMock->expects($this->exactly(2 * $calls))
            ->method('addFilter')
            ->withConsecutive(['status', 1, 'eq'], ['store_ids', self::STORE_ID, 'eq'])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->exactly($calls))
            ->method('addSortOrder')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->exactly($calls))
            ->method('create')
            ->will($this->returnValue($searchCriteriaMock));
        $this->categoryRepositoryMock->expects($this->exactly($calls))
            ->method('getList')
            ->with($searchCriteriaMock)
            ->will($this->returnValue($searchResultMock));
    }

    /**
     * Testing of addItem method
     */
    public function testAddItem()
    {
        $itemData = ['fieldName' => 'fieldValue'];
        $treeMock = $this->getMockBuilder(Tree::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $nodeMock = $this->getMockBuilder(Node::class)
            ->setMethods(['getTree', 'addChild'])
            ->disableOriginalConstructor()
            ->getMock();
        $nodeMock->expects($this->any())
            ->method('getTree')
            ->will($this->returnValue($treeMock));
        $newNodeMock = $this->getMockBuilder(Node::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->nodeFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'data' => $itemData,
                    'idField' => 'id',
                    'tree' => $treeMock,
                    'parent' => $nodeMock
                ]
            )
            ->willReturn($newNodeMock);

        $class = new \ReflectionClass($this->observer);
        $method = $class->getMethod('addItem');
        $method->setAccessible(true);
        $this->assertSame($newNodeMock, $method->invokeArgs($this->observer, [$itemData, $nodeMock]));
    }

    /**
     * Testing of isBlogHomeActive method
     *
     * @dataProvider isBlogHomeActiveDataProvider
     * @param bool $isBlogAction
     */
    public function testIsBlogHomeActive($isBlogAction)
    {
        $this->coreRegistryMock->expects($this->once())
            ->method('registry')
            ->with('blog_action')
            ->willReturn($isBlogAction);

        $class = new \ReflectionClass($this->observer);
        $method = $class->getMethod('isBlogHomeActive');
        $method->setAccessible(true);
        $this->assertEquals($isBlogAction, $method->invoke($this->observer));
    }

    /**
     * Testing of isCategoryActive method
     *
     * @dataProvider isCategoryActiveDataProvider
     * @param int $blogCategoryId
     * @param bool $expected
     */
    public function testIsCategoryActive($blogCategoryId, $expected)
    {
        $categoryMock = $this->getMockForAbstractClass(CategoryInterface::class);
        $categoryMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::CATEGORY_ID);
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('blog_category_id')
            ->willReturn($blogCategoryId);

        $class = new \ReflectionClass($this->observer);
        $method = $class->getMethod('isCategoryActive');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invokeArgs($this->observer, [$categoryMock]));
    }

    /**
     * Data provider for testExecute method
     *
     * @return array
     */
    public function executeDataProvider()
    {
        $categoryMock = $this->getMockForAbstractClass(CategoryInterface::class);
        $categoryMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::CATEGORY_ID);
        $categoryMock->expects($this->any())
            ->method('getName')
            ->willReturn(self::CATEGORY_NAME);
        return [
            'blog enabled, no categories' => [true, null],
            'blog enabled, one category' => [true, $categoryMock],
            'blog disabled' => [false]
        ];
    }

    /**
     * Data provider for testIsBlogHomeActive method
     *
     * @return array
     */
    public function isBlogHomeActiveDataProvider()
    {
        return ['blog home active' => [true], 'blog home not active' => [false]];
    }

    /**
     * Data provider for testIsCategoryActive method
     *
     * @return array
     */
    public function isCategoryActiveDataProvider()
    {
        return [
            'category active' => [self::CATEGORY_ID, true],
            'category not active' => [self::CATEGORY_ID + 1, false]
        ];
    }
}
