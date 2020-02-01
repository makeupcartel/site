<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Controller\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Controller\Index\Index;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Aheadworks\Blog\Model\Config as BlogConfig;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\Action\Context;

/**
 * Test for \Aheadworks\Blog\Controller\Index\Index
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Constants defined for test
     */
    const BLOG_TITLE_CONFIG_VALUE = 'Blog';
    const META_DESCRIPTION_CONFIG_VALUE = 'Meta description';
    const REFERER_URL = 'http://localhost';
    /**#@-*/

    /**
     * @var Index
     */
    private $action;

    /**
     * @var Page|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultPageMock;

    /**
     * @var Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultRedirectMock;

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
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->titleMock = $this->getMockBuilder(Title::class)
            ->setMethods(['set'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->pageConfigMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getTitle', 'setMetadata'])
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

        $configMock = $this->getMockBuilder(BlogConfig::class)
            ->setMethods(['getBlogTitle', 'getBlogMetaDescription'])
            ->disableOriginalConstructor()
            ->getMock();
        $configMock->expects($this->any())
            ->method('getBlogTitle')
            ->will($this->returnValue(self::BLOG_TITLE_CONFIG_VALUE));
        $configMock->expects($this->any())
            ->method('getBlogMetaDescription')
            ->will($this->returnValue(self::META_DESCRIPTION_CONFIG_VALUE));

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
            Index::class,
            [
                'context' => $context,
                'resultPageFactory' => $resultPageFactoryMock,
                'config' => $configMock
            ]
        );
    }

    /**
     * Testing return value of execute method
     */
    public function testExecuteResult()
    {
        $this->assertSame($this->resultPageMock, $this->action->execute());
    }

    /**
     * Testing that page config values is set
     */
    public function testExecutePageConfig()
    {
        $this->titleMock->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->equalTo(self::BLOG_TITLE_CONFIG_VALUE));
        $this->pageConfigMock->expects($this->atLeastOnce())
            ->method('setMetadata')
            ->with(
                $this->equalTo('description'),
                $this->equalTo(self::META_DESCRIPTION_CONFIG_VALUE)
            );
        $this->action->execute();
    }
}
