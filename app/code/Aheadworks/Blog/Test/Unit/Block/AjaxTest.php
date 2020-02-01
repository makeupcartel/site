<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block;

use Aheadworks\Blog\Block\Ajax;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\UrlInterface;
use Aheadworks\Blog\Model\Serialize\SerializeInterface;
use Aheadworks\Blog\Model\Serialize\Factory as SerializeFactory;

/**
 * Test for \Aheadworks\Blog\Block\Ajax
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AjaxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Ajax
     */
    private $block;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var SerializeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $contextMock = $objectManager->getObject(
            Context::class,
            [
                'layout' => $this->layoutMock,
                'urlBuilder' => $this->urlBuilderMock,
                'request' => $this->requestMock
            ]
        );
        $this->serializerMock = $this->getMockForAbstractClass(SerializeInterface::class);
        $serializeFactoryMock = $this->createPartialMock(SerializeFactory::class, ['create']);
        $serializeFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->serializerMock);

        $this->block = $objectManager->getObject(
            Ajax::class,
            [
                'context' => $contextMock,
                'serializeFactory' => $serializeFactoryMock
            ]
        );
    }

    /**
     * Testing of getScriptOptions method
     */
    public function testGetScriptOptions()
    {
        $isSecure = false;
        $url = 'https://ecommerce.aheadworks.com/aw_blog/block/render/id/1369/';
        $expected = '{"url":"https:\/\/ecommerce.aheadworks.com\/aw_blog\/block\/render\/id\/1369\/"}';

        $this->requestMock->expects($this->once())
            ->method('isSecure')
            ->willReturn($isSecure);

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(
                'aw_blog/block/render/',
                [
                    '_current' => true,
                    '_secure' => $isSecure,
                ]
            )->willReturn($url);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with(['url' => $url])
            ->willReturn($expected);

        $this->assertEquals($expected, $this->block->getScriptOptions());
    }
}
