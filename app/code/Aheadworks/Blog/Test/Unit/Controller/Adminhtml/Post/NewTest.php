<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Controller\Adminhtml\Post;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Controller\Adminhtml\Post\NewAction;
use Magento\Framework\Controller\Result\Forward;
use Magento\Backend\Model\View\Result\ForwardFactory;

/**
 * Test for \Aheadworks\Blog\Controller\Adminhtml\Post\NewAction
 */
class NewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var NewAction
     */
    private $action;

    /**
     * @var Forward|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultForwardMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->resultForwardMock = $this->getMockBuilder(Forward::class)
            ->setMethods(['forward'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultForwardMock->expects($this->any())
            ->method('forward')
            ->will($this->returnSelf());
        $resultForwardFactoryMock = $this->getMockBuilder(ForwardFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $resultForwardFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->resultForwardMock));

        $this->action = $objectManager->getObject(
            NewAction::class,
            ['resultForwardFactory' => $resultForwardFactoryMock]
        );
    }

    /**
     * Testing of return value of execute method
     */
    public function testExecuteResult()
    {
        $this->assertSame($this->resultForwardMock, $this->action->execute());
    }
}
