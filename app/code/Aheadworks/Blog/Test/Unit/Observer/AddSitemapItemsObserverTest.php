<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Observer;

use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Sitemap\ItemsProviderComposite;
use Aheadworks\Blog\Observer\AddSitemapItemsObserver;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Model\Sitemap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Event\Observer;

/**
 * Class AddSitemapItemsObserverTest
 * @package Aheadworks\Blog\Test\Unit\Observer
 */
class AddSitemapItemsObserverTest extends TestCase
{
    /**
     * @var ItemsProviderComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemsProviderMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var AddSitemapItemsObserver
     */
    private $observer;

    /**
     * Init mocks for tests
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->createMock(Config::class);
        $this->itemsProviderMock = $this->createMock(ItemsProviderComposite::class);
        $this->observer = $objectManager->getObject(
            AddSitemapItemsObserver::class,
            [
                'itemsProvider' => $this->itemsProviderMock,
                'config' => $this->configMock
            ]
        );
    }

    /**
     * Test execute method
     *
     * @param bool $isEnabled
     * @param array $items
     * @dataProvider executeProvider
     */
    public function testExecute($isEnabled, $items)
    {
        $storeId = 1;
        $sitemapMock = $this->createMock(Sitemap::class);
        $eventMock = $this->createMock(Event::class);
        $observerMock = $this->createConfiguredMock(Observer::class, ['getEvent' => $eventMock]);

        $eventMock->expects($this->once())
            ->method('__call')
            ->with('getObject')
            ->willReturn($sitemapMock);
        $sitemapMock->expects($this->once())
            ->method('__call')
            ->with('getStoreId')
            ->willReturn($storeId);
        $this->configMock->expects($this->once())
            ->method('isBlogEnabled')
            ->with($storeId)
            ->willReturn($isEnabled);
        $this->itemsProviderMock->expects($this->exactly($isEnabled ? 1 : 0))
            ->method('getItems')
            ->with($storeId)
            ->willReturn($items);
        $sitemapMock->expects($this->exactly($isEnabled ? 1 : 0))
            ->method('appendSitemapItems')
            ->with($items)
            ->willReturnSelf();

        $this->observer->execute($observerMock);
    }

    /**
     * @return array
     */
    public function executeProvider()
    {
        return [
            [true, []],
            [true, [$this->createMock(DataObject::class)]],
            [false, []]
        ];
    }
}
