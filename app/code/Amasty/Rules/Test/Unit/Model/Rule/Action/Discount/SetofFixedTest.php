<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Test\Unit\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule\Action\Discount\SetofFixed;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class SetofFixedTest
 *
 * @see SetofFixed
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreFile
 */
class SetofFixedTest extends AbstractRuleTest
{
    const RULE_DISCOUNT_AMOUNT = 50;

    /**
     * @covers SetofFixed::calculateDiscountForItems
     *
     * @throws \ReflectionException
     */
    public function testCalculateDiscountForItems()
    {
        $total = $this->prepareQuoteItems(false);

        $dataFactory = $this->initDiscountDataFactory();
        $productHelper = $this->initProductHelper();
        $priceCurrency = $this->initPriceCurrency();
        $rule = $this->initRule(false);

        /** @var MockObject|\Amasty\Rules\Model\RuleResolver $ruleResolver */
        $ruleResolver = $this->createPartialMock(\Amasty\Rules\Model\RuleResolver::class, ['getLinkId']);
        $ruleResolver->expects($this->any())->method('getLinkId')->will($this->returnValue(1));

        /** @var SetofFixed $action */
        $action = $this->objectManager->getObject(
            SetofFixed::class,
            [
                'discountDataFactory' => $dataFactory,
                'rulesProductHelper' => $productHelper,
                'priceCurrency' => $priceCurrency,
                'ruleResolver' => $ruleResolver
            ]
        );

        $actualTotal = 0;

        $this->invokeMethod($action, 'calculateDiscountForItems', [$total, $rule, $this->items, static::RULE_DISCOUNT_AMOUNT]);

        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discount */
        foreach (current($action::$cachedDiscount) as $discount) {
            $actualTotal += $discount->getBaseAmount();
        }

        $this->assertEquals(static::RULE_DISCOUNT_AMOUNT, ($total - $actualTotal));
    }

    /**
     * @covers SetofFixed::getBaseItemsPrice
     *
     * @throws \ReflectionException
     */
    public function testGetBaseItemsPrice()
    {
        $expectedTotal = $this->prepareQuoteItems(false);

        $action = $this->getMockBuilder(SetofFixed::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseItemsPrice'])
            ->getMock();
        $this->setProperty($action, 'validator', $this->initValidator());

        $actualTotal = $this->invokeMethod($action, 'getBaseItemsPrice', [$this->items]);

        $this->assertEquals($expectedTotal, $actualTotal);
    }
}
