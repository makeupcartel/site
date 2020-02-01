<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Controller\Adminhtml\Category;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Result\PageFactory;
use Aheadworks\Blog\Controller\Adminhtml\Category\Index;

/**
 * Test for \Aheadworks\Blog\Controller\Adminhtml\Category\Index
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Index
     */
    private $action;

    /**
     * @var Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $titleMock = $this->getMockBuilder(Title::class)
            ->setMethods(['prepend'])
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $pageConfigMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue($titleMock));
        $this->resultPageMock = $this->getMockBuilder(Page::class)
            ->setMethods(['setActiveMenu', 'getConfig'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $this->action = $objectManager->getObject(
            Index::class,
            ['resultPageFactory' => $resultPageFactoryMock]
        );
    }

    /**
     * Testing of return value of execute method
     */
    public function testExecuteResult()
    {
        $this->assertSame($this->resultPageMock, $this->action->execute());
    }
}
