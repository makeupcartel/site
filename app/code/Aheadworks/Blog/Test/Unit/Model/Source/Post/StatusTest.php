<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\Source\Post;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\Source\Post\Status;

/**
 * Test for \Aheadworks\Blog\Model\Source\Post\Status
 */
class StatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Status
     */
    private $sourceModel;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->sourceModel = $objectManager->getObject(Status::class);
    }

    /**
     * Testing of toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->assertTrue(is_array($this->sourceModel->toOptionArray()));
    }

    /**
     * Testing of getOptions method
     */
    public function testGetOptions()
    {
        $this->assertTrue(is_array($this->sourceModel->getOptions()));
    }
}
