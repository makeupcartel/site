<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Adminhtml\Post\Edit\Button;

use Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\Back as BackButton;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;

/**
 * Test for \Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\Back
 */
class BackTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    const BACK_URL = 'http://localhost/blog/post/index';

    /**
     * @var BackButton
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

        $urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo('*/*/'))
            ->will($this->returnValue(self::BACK_URL));

        $this->button = $objectManager->getObject(BackButton::class, ['urlBuilder' => $urlBuilderMock]);
    }

    /**
     * Testing of return value of getButtonData method
     */
    public function testGetButtonData()
    {
        $this->assertTrue(is_array($this->button->getButtonData()));
    }
}
