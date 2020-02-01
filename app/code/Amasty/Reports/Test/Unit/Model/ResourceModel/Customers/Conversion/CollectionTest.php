<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Test\Unit\Model\ResourceModel\Customers\Conversion;

use Amasty\Reports\Model\ResourceModel\Customers\Conversion\Collection;
use Amasty\Reports\Test\Unit\Traits;

/**
 * Class CollectionTest
 *
 * @see Collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Collection
     */
    private $model;

    protected function setUp()
    {
        $this->model = $this->createPartialMock(Collection::class, ['getDateTime']);
        parent::setUp();
    }

    /**
     * @covers Collection::getIntervalSelectAndGroupBy
     * @dataProvider getIntervalSelectAndGroupByDataProvider
     */
    public function testGetIntervalSelectAndGroupBy($isOrder, $filters, $result)
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->setMethods(['getSelect', 'reset'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);

        $collection->expects($this->any())->method('getSelect')->willReturn($collection);
        $request->expects($this->any())->method('getParam')->willReturn($filters);

        $this->setProperty($this->model, 'request', $request, Collection::class);

        $this->assertEquals(
            $result,
            $this->invokeMethod($this->model, 'getIntervalSelectAndGroupBy', [$collection, $isOrder])
        );
    }

    /**
     * Data provider for getIntervalSelectAndGroupBy test
     * @return array
     */
    public function getIntervalSelectAndGroupByDataProvider()
    {
        return [
            [true, ['interval' => 'year'], ['YEAR(DATE(orderTable.created_at))', 'YEAR(DATE(orderTable.created_at))']],
            [
                true,
                ['interval' => 'month'],
                ["CONCAT(YEAR(DATE(orderTable.created_at)), '-', MONTH(DATE(orderTable.created_at)))", 'MONTH(DATE(orderTable.created_at))']
            ],
            [
                true,
                ['interval' => 'week'],
                [
                    'CONCAT(ADDDATE(DATE(DATE(orderTable.created_at)), INTERVAL 1-DAYOFWEEK(DATE(orderTable.created_at)) DAY), " - ",'
                    . ' ADDDATE(DATE(DATE(orderTable.created_at)), INTERVAL 7-DAYOFWEEK(DATE(orderTable.created_at)) DAY))',
                    'WEEK(DATE(orderTable.created_at))'
                ]
            ],
            [false, ['interval' => 'day'], ['DATE(DATE(main_table.last_visit_at))', 'DATE(DATE(main_table.last_visit_at))']],
            [false, ['interval' => 'test'], ['DATE(DATE(main_table.last_visit_at))', 'DATE(DATE(main_table.last_visit_at))']],
        ];
    }

    /**
     * @covers Collection::getFromFilter
     */
    public function testGetFromFilter()
    {
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $helper = $this->createMock(\Amasty\Reports\Helper\Data::class);

        $request->expects($this->any())->method('getParam')->willReturnOnConsecutiveCalls(['from' => 'test'], []);
        $helper->expects($this->any())->method('getDefaultFromDate')->willReturn(date('Y-m-d'));
        $this->model->expects($this->any())->method('getDateTime')->willReturn(date('Y-m-d', strtotime('-7 day')));

        $this->setProperty($this->model, 'request', $request, Collection::class);
        $this->setProperty($this->model, 'helper', $helper, Collection::class);

        $this->assertEquals('test', $this->invokeMethod($this->model, 'getFromFilter', []));
        $this->assertEquals(date('Y-m-d', strtotime('-7 day')), $this->invokeMethod($this->model, 'getFromFilter', []));
    }

    /**
     * @covers Collection::getToFilter
     */
    public function testGetToFilter()
    {
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $helper = $this->getObjectManager()->getObject(\Amasty\Reports\Helper\Data::class);

        $request->expects($this->any())->method('getParam')->willReturnOnConsecutiveCalls(['to' => 'test'], []);
        $this->model->expects($this->any())->method('getDateTime')->willReturn(date('Y-m-d'));

        $this->setProperty($this->model, 'request', $request, Collection::class);
        $this->setProperty($this->model, 'helper', $helper, Collection::class);

        $this->assertEquals('test', $this->invokeMethod($this->model, 'getToFilter', []));
        $this->assertEquals(date('Y-m-d'), $this->invokeMethod($this->model, 'getToFilter', []));
    }
}
