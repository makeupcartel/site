<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Adminhtml\Post\Edit\Button;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\SaveAsDraft;

/**
 * Test for \Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\SaveAsDraft
 */
class SaveAsDraftTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button\SaveAsDraft
     */
    private $button;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->button = $objectManager->getObject(SaveAsDraft::class);
    }

    /**
     * Testing of return value of getButtonData method
     */
    public function testGetButtonData()
    {
        $this->assertTrue(is_array($this->button->getButtonData()));
    }
}
