<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Block\Disqus;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\DisqusConfig;

/**
 * Test for \Aheadworks\Blog\Block\Disqus
 */
class DisqusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Disqus
     */
    private $block;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['isCommentsEnabled'])
            ->disableOriginalConstructor()
            ->getMock();
        $disqusConfigMock = $this->getMockBuilder(DisqusConfig::class)
            ->setMethods(['getForumCode'])
            ->disableOriginalConstructor()
            ->getMock();
        $disqusConfigMock->expects($this->any())
            ->method('getForumCode')
            ->will($this->returnValue('forum_code'));

        $this->block = $objectManager->getObject(
            Disqus::class,
            ['config' => $this->configMock]
        );
    }

    /**
     * Testing of isCommentsEnabled method
     */
    public function testIsCommentsEnabled()
    {
        $isCommentsEnabled = true;
        $this->configMock->expects($this->any())
            ->method('isCommentsEnabled')
            ->will($this->returnValue($isCommentsEnabled));
        $this->assertEquals($isCommentsEnabled, $this->block->isCommentsEnabled());
    }

    /**
     * Testing of retrieving of count script url
     */
    public function testGetCountScriptUrl()
    {
        $this->assertTrue(is_string($this->block->getCountScriptUrl()));
    }

    /**
     * Testing of retrieving of embed script url
     */
    public function testGetEmbedScriptUrl()
    {
        $this->assertTrue(is_string($this->block->getEmbedScriptUrl()));
    }
}
