<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Block\Sharethis;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Blog\Block\Sharethis
 */
class SharethisTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    const POST_TITLE = 'Post';

    /**
     * @var Sharethis
     */
    private $block;

    /**
     * @var Url|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlMock;

    /**
     * @var PostInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->urlMock = $this->getMockBuilder(Url::class)
            ->setMethods(['getPostUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->postMock = $this->getMockForAbstractClass(PostInterface::class);
        $this->postMock->expects($this->any())
            ->method('getTitle')
            ->will($this->returnValue(self::POST_TITLE));

        $this->block = $objectManager->getObject(Sharethis::class, ['url' => $this->urlMock]);
    }

    /**
     * Testing of set a post
     */
    public function testSetPost()
    {
        $this->assertSame($this->block, $this->block->setPost($this->postMock));
    }

    /**
     * Testing of retrieving of share url
     */
    public function testGetShareUrl()
    {
        $postUrl = 'http://localhost/blog/post';
        $this->urlMock->expects($this->once())
            ->method('getPostUrl')
            ->with($this->equalTo($this->postMock))
            ->will($this->returnValue($postUrl));
        $this->block->setPost($this->postMock);
        $this->assertEquals($postUrl, $this->block->getShareUrl());
    }

    /**
     * Testing of retrieving of share text
     */
    public function testGetShareText()
    {
        $this->block->setPost($this->postMock);
        $this->assertEquals(self::POST_TITLE, $this->block->getShareText());
    }
}
