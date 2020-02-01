<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Test\Unit\Cron;

use Amasty\Reports\Cron\Abandoned;
use Amasty\Reports\Test\Unit\Traits;
use Magento\Framework\Flag;

/**
 * Class AbandonedTest
 *
 * @see Abandoned
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AbandonedTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Abandoned
     */
    private $model;

    /**
     * @covers Abandoned::getLastExecution
     *
     * @dataProvider getLastExecutionDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetLastExecution($flagValue, $currentValue, $expectedValue)
    {
        $this->model = $this->createPartialMock(Abandoned::class, ['getFlag', 'getCurrentExecution']);
        $flag = $this->createMock(Flag::class);
        $date = $this->createMock(\Magento\Framework\Stdlib\DateTime\DateTime::class);

        $this->model->expects($this->once())->method('getFlag')->willReturn($flag);
        $this->model->expects($this->once())->method('getFlag')->willReturn($flag);
        $date->expects($this->any())->method('gmtTimestamp')->willReturn($currentValue);
        $flag->expects($this->once())->method('loadSelf')->willReturn($flag);
        $flag->expects($this->once())->method('getFlagData')->willReturn($flagValue);
        $flag->expects($this->once())->method('setFlagData')->willReturn($flag);
        $flag->expects($this->once())->method('save')->willReturn($flag);

        $this->setProperty($this->model, 'date', $date, Abandoned::class);

        $actualValue = $this->invokeMethod($this->model, 'getLastExecution');

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * Data provider for getLastExecution test
     * @return array
     */
    public function getLastExecutionDataProvider()
    {
        return [
            [0, 777, -59223],
            [778, 777, 778]
        ];
    }
}
