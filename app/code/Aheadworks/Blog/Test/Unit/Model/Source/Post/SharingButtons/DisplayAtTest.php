<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\Source\Post\SharingButtons;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\Source\Post\SharingButtons\DisplayAt;

/**
 * Test for \Aheadworks\Blog\Model\Source\Post\SharingButtons\DisplayAt
 */
class DisplayAtTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DisplayAt
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
        $this->sourceModel = $objectManager->getObject(DisplayAt::class);
    }

    /**
     * Testing of toOptionArray method
     */
    public function testToOptionArray()
    {
        $this->assertTrue(is_array($this->sourceModel->toOptionArray()));
    }
}
