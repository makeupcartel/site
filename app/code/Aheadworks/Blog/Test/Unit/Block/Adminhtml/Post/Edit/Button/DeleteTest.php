<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Adminhtml\Post\Edit\Button;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\Delete as DeleteButton;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;

/**
 * Test for \Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\Delete
 */
class DeleteTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Button constants defined for test
     */
    const DELETE_URL = 'http://localhost/blog/post/delete/post_id/1';
    const POST_ID = 1;
    /**#@-*/

    /**
     * @var \Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\Delete
     */
    private $button;

    /**
     * @var DeleteButton|\PHPUnit_Framework_MockObject_MockObject
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

        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);

        $postRepositoryMock = $this->getMockForAbstractClass(PostRepositoryInterface::class);
        $postRepositoryMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo(self::POST_ID))
            ->will($this->returnValue($this->getMockForAbstractClass(PostInterface::class)));

        $this->button = $objectManager->getObject(
            DeleteButton::class,
            [
                'request' => $this->requestMock,
                'urlBuilder' => $this->urlBuilderMock,
                'postRepository' => $postRepositoryMock
            ]
        );
    }

    /**
     * Testing of return value of getButtonData method
     *
     * @dataProvider getButtonDataDataProvider
     * @param int|null $postId
     */
    public function testGetButtonData($postId)
    {
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->willReturn($postId);
        if ($postId) {
            $this->urlBuilderMock->expects($this->once())
                ->method('getUrl')
                ->with(
                    $this->equalTo('*/*/delete'),
                    $this->equalTo(['id' => self::POST_ID])
                )
                ->will($this->returnValue(self::DELETE_URL));
            $this->assertNotEmpty($this->button->getButtonData());
        } else {
            $this->assertEmpty($this->button->getButtonData());
        }
    }

    /**
     * Data provider for testGetButtonData method
     *
     * @return array
     */
    public function getButtonDataDataProvider()
    {
        return [
            'post id specified' => [self::POST_ID],
            'post id not specified' => [null]
        ];
    }
}
