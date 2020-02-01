<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model;

use Aheadworks\Blog\Api\Data\PostInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\PostRegistry;

/**
 * Test for \Aheadworks\Blog\Model\PostRegistry
 */
class PostRegistryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PostRegistry
     */
    private $postRegistry;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->postRegistry = $objectManager->getObject(PostRegistry::class);
    }

    /**
     * Testing of retrieve method on null
     */
    public function testRetrieveNull()
    {
        $postId = 1;

        $this->assertNull($this->postRegistry->retrieve($postId));
    }

    /**
     * Testing of retrieve method on object
     */
    public function testRetrieveObject()
    {
        $postId = 1;

        $postMock = $this->getMockForAbstractClass(PostInterface::class);
        $postMock->expects($this->once())
            ->method('getId')
            ->willReturn($postId);
        $this->postRegistry->push($postMock);
        $this->assertEquals($postMock, $this->postRegistry->retrieve($postId));
    }

    /**
     * Testing remove an instance
     */
    public function testRemove()
    {
        $postId = 1;

        $postMock = $this->getMockForAbstractClass(PostInterface::class);
        $postMock->expects($this->once())
            ->method('getId')
            ->willReturn($postId);
        $this->postRegistry->push($postMock);
        $postFromReg = $this->postRegistry->retrieve($postId);
        $this->assertEquals($postMock, $postFromReg);
        $this->postRegistry->remove($postId);
        $this->assertNull($this->postRegistry->retrieve($postId));
    }
}
