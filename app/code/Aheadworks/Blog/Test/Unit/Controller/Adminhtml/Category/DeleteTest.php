<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Controller\Adminhtml\Category\Delete;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Magento\Backend\App\Action\Context;

/**
 * Test for \Aheadworks\Blog\Controller\Adminhtml\Category\Delete
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var int
     */
    const CATEGORY_ID = 1;

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
     * @var CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
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
                'categoryRepository' => $this->categoryRepositoryMock
            ]
        );
    }

    /**
     * Testing of redirect if category id request param is not presented
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
     * Testing of redirect if category id request param is presented
     */
    public function testExecuteRedirectCategoryIdParam()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn(self::CATEGORY_ID);
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
            ->willReturn(self::CATEGORY_ID);
        $this->categoryRepositoryMock->expects($this->any())
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
     * Testing that category is deleted
     */
    public function testExecuteCategoryDelete()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn(self::CATEGORY_ID);
        $this->categoryRepositoryMock->expects($this->once())
            ->method('deleteById')
            ->with($this->equalTo(self::CATEGORY_ID));
        $this->action->execute();
    }

    /**
     * Testing that success message is added if category is deleted
     */
    public function testExecuteSuccessMessage()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn(self::CATEGORY_ID);
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
            ->willReturn(self::CATEGORY_ID);
        $this->categoryRepositoryMock->expects($this->any())
            ->method('deleteById')
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(__('Cannot delete.'))
            );
        $this->messageManagerMock->expects($this->atLeastOnce())->method('addErrorMessage');
        $this->action->execute();
    }
}
