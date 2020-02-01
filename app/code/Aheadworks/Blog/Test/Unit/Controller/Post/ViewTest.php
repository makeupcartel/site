<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Controller\Post;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Controller\Post\View;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\Message\ManagerInterface;
use Aheadworks\Blog\Model\Url;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\Action\Context;
use Aheadworks\Blog\Model\Source\Config\Seo\UrlType;
use Aheadworks\Blog\Model\Config as BlogConfig;
use Aheadworks\Blog\Model\Url\TypeResolver as UrlTypeResolver;
use Aheadworks\Blog\Controller\Checker as DataChecker;

/**
 * Test for \Aheadworks\Blog\Controller\Post\View
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const POST_ID = 1;
    const POST_STATUS = 'publication';
    const POST_TITLE = 'Post title';
    const POST_META_TITLE = 'Meta title';
    const POST_META_DESCRIPTION = 'Meta description';
    const CATEGORY_ID = 1;
    const STORE_ID = 1;
    const REFERER_URL = 'http://localhost';
    const POST_URL = 'http://localhost/post';
    /**#@-*/

    /**
     * @var array
     */
    private $postStoreId = [self::STORE_ID, 2];

    /**
     * @var array
     */
    private $postCategoryIds = [self::CATEGORY_ID, 2];

    /**
     * @var View
     */
    private $action;

    /**
     * @var Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageMock;

    /**
     * @var Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    private $forwardMock;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageConfigMock;

    /**
     * @var Title|\PHPUnit_Framework_MockObject_MockObject
     */
    private $titleMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var PostRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postRepositoryMock;

    /**
     * @var PostInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postMock;

    /**
     * @var DataChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataCheckerMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->titleMock = $this->getMockBuilder(Title::class)
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getTitle', 'setMetadata', 'addRemotePageAsset'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($this->titleMock));
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->setMethods(['getConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($this->pageConfigMock));
        $resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->resultPageMock));

        $this->forwardMock = $this->getMockBuilder(Forward::class)
            ->setMethods(['setModule', 'setController', 'forward'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->forwardMock->expects($this->any())
            ->method('setModule')
            ->will($this->returnSelf());
        $this->forwardMock->expects($this->any())
            ->method('setController')
            ->will($this->returnSelf());
        $this->forwardMock->expects($this->any())
            ->method('forward')
            ->will($this->returnSelf());
        $resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultForwardFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->forwardMock));

        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::STORE_ID));
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeManagerMock->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($storeMock));

        $this->postMock = $this->getMockForAbstractClass(PostInterface::class);
        $this->postRepositoryMock = $this->getMockForAbstractClass(PostRepositoryInterface::class);
        $this->postRepositoryMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo(self::POST_ID))
            ->will($this->returnValue($this->postMock));

        $this->urlMock = $this->getMockBuilder(Url::class)
            ->setMethods(['getPostUrl', 'getCanonicalUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlMock->expects($this->any())
            ->method('getPostUrl')
            ->with($this->equalTo($this->postMock))
            ->will($this->returnValue(self::POST_URL));

        $this->urlMock->expects($this->any())
            ->method('getCanonicalUrl')
            ->with($this->equalTo($this->postMock))
            ->will($this->returnValue(self::POST_URL));

        $urlTypeMock = $this->getMockBuilder(UrlTypeResolver::class)
            ->setMethods(['isCategoryExcl'])
            ->disableOriginalConstructor()
            ->getMock();
        $urlTypeMock->expects($this->any())
            ->method('isCategoryExcl')
            ->will($this->returnValue(true));
        $this->dataCheckerMock = $this->getMockBuilder(DataChecker::class)
            ->setMethods(['isPostVisible'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->resultRedirectMock));

        $configMock = $this->getMockBuilder(BlogConfig::class)
            ->setMethods(['getSeoUrlType'])
            ->disableOriginalConstructor()
            ->getMock();
        $configMock->expects($this->any())->method('getSeoUrlType')
            ->with(self::STORE_ID)
            ->will($this->returnValue(UrlType::URL_EXC_CATEGORY));

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $redirectMock = $this->getMockForAbstractClass(RedirectInterface::class);
        $redirectMock->expects($this->any())
            ->method('getRefererUrl')
            ->will($this->returnValue(self::REFERER_URL));
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'redirect' => $redirectMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $resultRedirectFactoryMock
            ]
        );

        $this->action = $objectManager->getObject(
            View::class,
            [
                'context' => $context,
                'resultPageFactory' => $resultPageFactoryMock,
                'resultForwardFactory' => $resultForwardFactoryMock,
                'storeManager' => $storeManagerMock,
                'postRepository' => $this->postRepositoryMock,
                'url' => $this->urlMock,
                'config' => $configMock,
                'urlTypeResolver' => $urlTypeMock,
                'dataChecker' => $this->dataCheckerMock
            ]
        );
    }

    /**
     * Prepare post mock
     *
     * @param string $status
     * @param array|null $storeId
     * @param array|null $categoryIds
     */
    private function preparePostMock($status = self::POST_STATUS, $storeId = null, $categoryIds = null)
    {
        if (!$storeId) {
            $storeId = $this->postStoreId;
        }
        if (!$categoryIds) {
            $categoryIds = $this->postCategoryIds;
        }
        $this->postMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(self::POST_ID));
        $this->postMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue(self::POST_TITLE));
        $this->postMock->expects($this->any())
            ->method('getMetaTitle')
            ->will($this->returnValue(self::POST_META_TITLE));
        $this->postMock->expects($this->any())
            ->method('getMetaDescription')
            ->will($this->returnValue(self::POST_META_DESCRIPTION));
        $this->postMock->expects($this->any())
            ->method('getCategoryIds')
            ->will($this->returnValue($categoryIds));
        $this->postMock->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue($status));
        $this->postMock->expects($this->any())
            ->method('getStoreIds')
            ->will($this->returnValue($storeId));
    }

    /**
     * Allow or disallow post
     * @param bool $status
     */
    public function isPostVisible(bool $status)
    {
        $this->dataCheckerMock->expects($this->any())
            ->method('isPostVisible')
            ->with($this->postMock, self::STORE_ID)
            ->willReturn($status);
    }

    /**
     * Testing return value of execute method
     */
    public function testExecuteResult()
    {
        $this->preparePostMock();
        $this->isPostVisible(true);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['post_id', null, self::POST_ID],
                    ['blog_category_id', null, null]
                ]
            );
        $this->assertSame($this->resultPageMock, $this->action->execute());
    }

    /**
     * Testing redirect if error is occur
     */
    public function testExecuteErrorRedirect()
    {
        $this->preparePostMock();
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['post_id', null, self::POST_ID],
                    ['blog_category_id', null, null]
                ]
            );
        $this->postRepositoryMock->expects($this->any())
            ->method('get')
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(__('Not found.'))
            );
        $this->assertSame($this->resultRedirectMock, $this->action->execute());
    }

    /**
     * Testing that error message is added if error is occur
     */
    public function testExecuteErrorMessage()
    {
        $this->preparePostMock();
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['post_id', null, self::POST_ID],
                    ['blog_category_id', null, null]
                ]
            );
        $this->postRepositoryMock->expects($this->any())
            ->method('get')
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(__('Not found.'))
            );
        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with($this->equalTo('Not found.'));
        $this->action->execute();
    }

    /**
     * Testing that page config values is set
     */
    public function testExecutePageConfig()
    {
        $shortContent = 'short content';

        $this->preparePostMock();
        $this->isPostVisible(true);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['post_id', null, self::POST_ID],
                    ['blog_category_id', null, null]
                ]
            );
        $this->titleMock->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->equalTo(self::POST_META_TITLE));
        $this->pageConfigMock->expects($this->at(1))
            ->method('setMetadata')
            ->with(
                $this->equalTo('description'),
                $this->equalTo(self::POST_META_DESCRIPTION)
            );
        $this->action->execute();
    }

    /**
     * Testing of forwarding to noroute action if post is not valid
     *
     * @dataProvider executeForwardDataProvider
     */
    public function testExecuteForward($status, $storeId)
    {
        $this->preparePostMock($status, $storeId);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['post_id', null, self::POST_ID],
                    ['blog_category_id', null, null]
                ]
            );
        $this->isPostVisible(false);
        $this->forwardMock->expects($this->atLeastOnce())
            ->method('setModule')
            ->with($this->equalTo('cms'));
        $this->forwardMock->expects($this->atLeastOnce())
            ->method('setController')
            ->with($this->equalTo('noroute'));
        $this->forwardMock->expects($this->once())
            ->method('forward')
            ->with($this->equalTo('index'));
        $this->action->execute();
    }

    /**
     * Testing of redirect from post url with category id to the short post url (without category id)
     *
     * @param int|null $categoryId
     * @dataProvider executeRedirectIfContainsCategoryIdDataProvider
     */
    public function testExecuteRedirectIfContainsCategoryId($categoryId)
    {
        $this->preparePostMock(self::POST_STATUS, null, [self::CATEGORY_ID]);
        $this->isPostVisible(true);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['post_id', null, self::POST_ID],
                    ['blog_category_id', null, $categoryId]
                ]
            );
        if ($categoryId) {
            $this->resultRedirectMock->expects($this->atLeastOnce())
                ->method('setUrl')
                ->with($this->equalTo(self::POST_URL));
            $this->assertSame($this->resultRedirectMock, $this->action->execute());
        } else {
            $this->action->execute();
        }
    }

    /**
     * @return array
     */
    public function executeForwardDataProvider()
    {
        return [
            'post is draft' => ['draft', null],
            'post from another store view' => ['published', [2]]
        ];
    }

    /**
     * @return array
     */
    public function executeRedirectIfContainsCategoryIdDataProvider()
    {
        return [[self::CATEGORY_ID], [2]];
    }
}
