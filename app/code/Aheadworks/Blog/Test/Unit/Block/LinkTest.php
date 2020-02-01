<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Block\Link;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Template\Context;

/**
 * Test for \Aheadworks\Blog\Block\Link
 */
class LinkTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Link constants defined for test
     */
    const LINK_URL = 'http://localhost';
    const LINK_TITLE = 'Link';
    const LINK_LABEL = 'Link';
    /**#@-*/

    /**
     * @var Link
     */
    private $block;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $escaperMock = $this->getMockBuilder(Escaper::class)
            ->setMethods(['escapeHtml'])
            ->disableOriginalConstructor()
            ->getMock();
        $escaperMock->expects($this->any())
            ->method('escapeHtml')
            ->will($this->returnArgument(0));
        $contextMock = $objectManager->getObject(
            Context::class,
            ['escaper' => $escaperMock]
        );

        $this->block = $objectManager->getObject(
            Link::class,
            ['context' => $contextMock]
        );
    }

    /**
     * Testing of getHref method
     */
    public function testGetHref()
    {
        $this->block->setData('href', self::LINK_URL);
        $this->assertEquals(self::LINK_URL, $this->block->getHref());
    }

    /**
     * Testing of _toHtml method
     */
    public function testToHtml()
    {
        $this->block->setData('href', self::LINK_URL);
        $this->block->setData('title', self::LINK_TITLE);
        $this->block->setData('label', self::LINK_LABEL);
        $this->assertEquals(
            '<a href="' . self::LINK_URL . '" title="' . self::LINK_TITLE . '" >' . self::LINK_LABEL . '</a>',
            $this->block->toHtml()
        );
    }
}
