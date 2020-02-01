<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Adminhtml\Post\Edit\Button;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\Update;
use Magento\Framework\App\RequestInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;

/**
 * Test for \Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\Update
 */
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var int
     */
    const POST_ID = 1;

    /**
     * @var Update
     */
    private $button;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->any())
            ->method('getParam')
            ->with($this->equalTo('id'))
            ->will($this->returnValue(self::POST_ID));

        $postMock = $this->getMockForAbstractClass(PostInterface::class);
        $postRepositoryMock = $this->getMockForAbstractClass(PostRepositoryInterface::class);
        $postRepositoryMock->expects($this->any())
            ->method('get')
            ->with($this->equalTo(self::POST_ID))
            ->will($this->returnValue($postMock));

        $this->button = $objectManager->getObject(
            Update::class,
            [
                'request' => $requestMock,
                'postRepository' => $postRepositoryMock
            ]
        );
    }

    /**
     * Testing of return value of getButtonData method
     */
    public function testGetButtonData()
    {
        $this->assertTrue(is_array($this->button->getButtonData()));
    }
}
