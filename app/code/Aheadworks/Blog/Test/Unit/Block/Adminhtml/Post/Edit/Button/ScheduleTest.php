<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Adminhtml\Post\Edit\Button;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\Schedule;

/**
 * Test for \Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\Schedule
 */
class ScheduleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Schedule
     */
    private $button;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->button = $objectManager->getObject(Schedule::class);
    }

    /**
     * Testing of return value of getButtonData method
     */
    public function testGetButtonData()
    {
        $this->assertTrue(is_array($this->button->getButtonData()));
    }
}
