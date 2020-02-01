<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Ui\Component\Post\Form\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Ui\Component\Post\Form\Element\Stores;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\System\Store;

/**
 * Test for \Aheadworks\Blog\Ui\Component\Post\Form\Element\Stores
 */
class StoresTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Stores
     */
    private $stores;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $processorMock = $this->getMockBuilder(Processor::class)
            ->setMethods(['register'])
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockForAbstractClass(ContextInterface::class);
        $contextMock->expects($this->exactly(2))
            ->method('getProcessor')
            ->will($this->returnValue($processorMock));

        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $storeOptionsMock = $this->getMockBuilder(Store::class)
            ->setMethods(['toOptionArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeOptionsMock->expects($this->any())
            ->method('toOptionArray')
            ->will(
                $this->returnValue([['value' => 'optionValue', 'label' => 'optionLabel']])
            );

        $this->stores = $objectManager->getObject(
            Stores::class,
            [
                'context' => $contextMock,
                'storeManager' => $this->storeManagerMock,
                'storeOptions' => $storeOptionsMock,
                'data' => ['config' => []]
            ]
        );
    }

    /**
     * Testing of prepare component configuration if single store mode
     */
    public function testPrepareSingleStore()
    {
        $this->storeManagerMock->expects($this->once())
            ->method('hasSingleStore')
            ->willReturn(true);
        $this->stores->prepare();
        $config = $this->stores->getData('config');
        $this->assertArrayHasKey('visible', $config);
        $this->assertFalse($config['visible']);
    }

    /**
     * Testing of prepare component configuration if multi store mode
     */
    public function testPrepareMultiStore()
    {
        $this->storeManagerMock->expects($this->once())
            ->method('hasSingleStore')
            ->willReturn(false);
        $this->stores->prepare();
        $config = $this->stores->getData('config');
        $this->assertArrayNotHasKey('visible', $config);
    }
}
