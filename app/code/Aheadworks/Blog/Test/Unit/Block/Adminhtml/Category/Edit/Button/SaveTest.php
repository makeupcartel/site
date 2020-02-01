<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Adminhtml\Category\Edit\Button;

use Aheadworks\Blog\Block\Adminhtml\Category\Edit\Button\Save as SaveButton;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Blog\Block\Adminhtml\Category\Edit\Button\Save
 */
class SaveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SaveButton
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
        $this->button = $objectManager->getObject(SaveButton::class);
    }

    /**
     * Testing of return value of getButtonData method
     */
    public function testGetButtonData()
    {
        $this->assertTrue(is_array($this->button->getButtonData()));
    }
}
