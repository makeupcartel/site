<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Adminhtml\Category\Edit\Button;

use Aheadworks\Blog\Block\Adminhtml\Category\Edit\Button\SaveAndContinue as SaveAndContinueButton;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Blog\Block\Adminhtml\Category\Edit\Button\SaveAndContinue
 */
class SaveAndContinueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SaveAndContinueButton
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
        $this->button = $objectManager->getObject(SaveAndContinueButton::class);
    }

    /**
     * Testing of return value of getButtonData method
     */
    public function testGetButtonData()
    {
        $this->assertTrue(is_array($this->button->getButtonData()));
    }
}
