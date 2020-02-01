<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Controller\Adminhtml\Post;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Controller\Adminhtml\Post\Edit;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Page\Title;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\App\Action\Context;

/**
 * Test for \Aheadworks\Blog\Controller\Adminhtml\Post\Edit
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var int
     */
    const POST_ID = 1;

    /**
     * @var Edit
     */
    private $action;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

    /**
     * @var Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Title|\PHPUnit_Framework_MockObject_MockObject
     */
    private $titleMock;

    /**
     * @var PostRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->postRepositoryMock = $this->getMockForAbstractClass(PostRepositoryInterface::class);
        $this->titleMock = $this->getMockBuilder(Title::class)
            ->setMethods(['prepend'])
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($this->titleMock));
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->setMethods(['setActiveMenu', 'getConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultPageMock->expects($this->any())
            ->method('setActiveMenu')
            ->will($this->returnSelf());
        $this->resultPageMock->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($pageConfigMock));
        $resultPageFactoryMock = $this->getMockBuilder(PageFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultPageFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->resultPageMock));

        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->setMethods(['setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock->expects($this->any())
            ->method('setPath')
            ->will($this->returnSelf());
        $resultRedirectFactoryMock = $this->getMockBuilder(RedirectFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultRedirectFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->resultRedirectMock));

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->messageManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'request' => $this->requestMock,
                'messageManager' => $this->messageManagerMock,
                'resultRedirectFactory' => $resultRedirectFactoryMock
            ]
        );

        $this->action = $objectManager->getObject(
            Edit::class,
            [
                'context' => $context,
                'postRepository' => $this->postRepositoryMock,
                'resultPageFactory' => $resultPageFactoryMock
            ]
        );
    }

    /**
     * Testing of result of execution if post exists
     */
    public function testExecuteResultPostExists()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn(self::POST_ID);
        $this->assertSame($this->resultPageMock, $this->action->execute());
    }

    /**
     * Testing of redirection if post is not exists
     */
    public function testExecuteRedirectPostNotExists()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn(self::POST_ID);
        $this->postRepositoryMock->expects($this->any())
            ->method('get')
            ->willThrowException(
                new \Magento\Framework\Exception\NoSuchEntityException()
            );
        $this->resultRedirectMock->expects($this->atLeastOnce())
            ->method('setPath')
            ->with($this->equalTo('*/*/'));
        $this->assertSame($this->resultRedirectMock, $this->action->execute());
    }

    /**
     * Testing that error message is added if post is not exists
     */
    public function testExecuteErrorMessage()
    {
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
                ->willReturn(self::POST_ID);
        $this->postRepositoryMock->expects($this->any())
            ->method('get')
            ->willThrowException($exception);
        $this->messageManagerMock->expects($this->once())
            ->method('addException')
            ->with($this->equalTo($exception));
        $this->action->execute();
    }
}
