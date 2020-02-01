<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Ui\Component\Post\Form\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Ui\Component\Post\Form\Element\CommentsLink;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\Blog\Api\CommentsServiceInterface;

/**
 * Test for \Aheadworks\Blog\Ui\Component\Post\Form\Element\CommentsLink
 */
class CommentsLinkTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    const DISQUS_ADMIN_URL = 'https://forum_code.disqus.com/admin/';

    /**
     * @var CommentsLink
     */
    private $commentsLink;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sessionMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $processorMock = $this->getMockBuilder(Processor::class)
            ->setMethods(['register'])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockForAbstractClass(ContextInterface::class);
        $contextMock->expects($this->exactly(2))
            ->method('getProcessor')
            ->will($this->returnValue($processorMock));

        $commentsServiceMock = $this->getMockForAbstractClass(CommentsServiceInterface::class);
        $commentsServiceMock->expects($this->any())
            ->method('getModerateUrl')
            ->will($this->returnValue(self::DISQUS_ADMIN_URL));

        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->setMethods(['isAllowed'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->commentsLink = $objectManager->getObject(
            CommentsLink::class,
            [
                'context' => $contextMock,
                'commentsService' => $commentsServiceMock,
                'authSession' => $this->sessionMock,
                'data' => ['config' => []]
            ]
        );
    }

    /**
     * Testing of prepare component configuration when comments management is allowed
     */
    public function testPrepareCommentsAllowed()
    {
        $this->sessionMock->expects($this->once())
            ->method('isAllowed')
            ->with($this->equalTo('Aheadworks_Blog::comments'))
            ->willReturn(true);
        $this->commentsLink->prepare();
        $config = $this->commentsLink->getData('config');
        $this->assertArrayHasKey('url', $config);
        $this->assertArrayHasKey('linkLabel', $config);
        $this->assertEquals(self::DISQUS_ADMIN_URL, $config['url']);
    }

    /**
     * Testing of prepare component configuration when comments management is not allowed
     */
    public function testPrepareCommentsIsNotAllowed()
    {
        $this->sessionMock->expects($this->once())
            ->method('isAllowed')
            ->with($this->equalTo('Aheadworks_Blog::comments'))
            ->willReturn(false);
        $this->commentsLink->prepare();
        $config = $this->commentsLink->getData('config');
        $this->assertArrayNotHasKey('url', $config);
        $this->assertArrayNotHasKey('linkLabel', $config);
    }
}
