<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Ui\Component\Post\Form\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Blog\Ui\Component\Post\Form\Element\StatusLabel;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\Blog\Model\Source\Post\Status;

/**
 * Test for \Aheadworks\Blog\Ui\Component\Post\Form\Element\StatusLabel
 */
class StatusLabelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StatusLabel
     */
    private $statusLabel;

    /**
     * @var array
     */
    private $sourceArray = ['optionName' => 'optionValue'];

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

        $statusSourceMock = $this->getMockBuilder(Status::class)
            ->setMethods(['getOptions'])
            ->disableOriginalConstructor()
            ->getMock();
        $statusSourceMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($this->sourceArray));

        $this->statusLabel = $objectManager->getObject(
            StatusLabel::class,
            [
                'context' => $contextMock,
                'statusSource' => $statusSourceMock,
                'data' => ['config' => []]
            ]
        );
    }

    /**
     * Testing of prepare component configuration
     */
    public function testPrepare()
    {
        $this->statusLabel->prepare();
        $config = $this->statusLabel->getData('config');
        $this->assertArrayHasKey('statusOptions', $config);
        $this->assertEquals($this->sourceArray, $config['statusOptions']);
    }
}
