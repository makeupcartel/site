<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Controller\Adminhtml\Post;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Aheadworks\Blog\Controller\Adminhtml\Post\Delete;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\App\Action\Context;

/**
 * Test for \Aheadworks\Blog\Controller\Adminhtml\Post\Delete
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var int
     */
    const POST_ID = 1;

    /**
     * @var Delete
     */
    private $action;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageManagerMock;

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
            Delete::class,
            [
                'context' => $context,
                'postRepository' => $this->postRepositoryMock
            ]
        );
    }

    /**
     * Testing of redirect if post id request param is not presented
     */
    public function testExecuteRedirect()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn(null);
        $this->resultRedirectMock->expects($this->atLeastOnce())
            ->method('setPath')
            ->with($this->equalTo('*/*/'));
        $this->assertSame($this->resultRedirectMock, $this->action->execute());
    }

    /**
     * Testing of redirect if post id request param is presented
     */
    public function testExecuteRedirectPostIdParam()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn(self::POST_ID);
        $this->resultRedirectMock->expects($this->atLeastOnce())
            ->method('setPath')
            ->with($this->equalTo('*/*/'));
        $this->assertSame($this->resultRedirectMock, $this->action->execute());
    }

    /**
     * Testing of redirect if error is occur
     */
    public function testExecuteRedirectException()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn(self::POST_ID);
        $this->postRepositoryMock->expects($this->any())
            ->method('deleteById')
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(__('Cannot delete.'))
            );
        $this->resultRedirectMock->expects($this->atLeastOnce())
            ->method('setPath')
            ->with($this->equalTo('*/*/'));
        $this->assertSame($this->resultRedirectMock, $this->action->execute());
    }

    /**
     * Testing that post is deleted
     */
    public function testExecutePostDelete()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn(self::POST_ID);
        $this->postRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo(self::POST_ID));
        $this->action->execute();
    }

    /**
     * Testing that success message is added if post is deleted
     */
    public function testExecuteSuccessMessage()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn(self::POST_ID);
        $this->messageManagerMock->expects($this->once())->method('addSuccessMessage');
        $this->action->execute();
    }

    /**
     * Testing that error message is added if error is occur
     */
    public function testExecuteErrorMessage()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn(self::POST_ID);
        $this->postRepositoryMock->expects($this->any())
            ->method('deleteById')
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(__('Cannot delete.'))
            );
        $this->messageManagerMock->expects($this->atLeastOnce())->method('addErrorMessage');
        $this->action->execute();
    }
}
