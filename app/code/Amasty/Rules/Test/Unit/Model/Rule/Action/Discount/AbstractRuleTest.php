<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Rules
 */


namespace Amasty\Rules\Test\Unit\Model\Rule\Action\Discount;

use Amasty\Rules\Model\Rule;
use Amasty\Rules\Model\Rule\Action\Discount\AbstractRule;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class AbstractDiscountActionTest
 *
 * @codingStandardsIgnoreFile
 */
class AbstractRuleTest extends \PHPUnit\Framework\TestCase
{
    /**#@+
     * Required data of AbstractRule|Rule object
     */
    const ITEMS_COUNT = 10;
    const RULE_DISCOUNT_STEP = 2;
    const RULE_SIMPLE_ACTION = '';
    const RULE_DISCOUNT_QTY = 0;
    const RULE_DISCOUNT_AMOUNT = 10;
    /**#@-*/

    /**
     * @var \Magento\Quote\Model\Quote|MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Quote\Model\Quote\Address|MockObject
     */
    protected $address;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @throws \ReflectionException
     */
    protected function setUp()
    {
        $this->initQuote();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    /**
     * Test for getSortedItems function.
     * Used validateItems function replaced with stub.
     *
     * @covers \Amasty\Rules\Model\Rule\Action\Discount\AbstractRule::getSortedItems
     * @throws \ReflectionException
     */
    public function testGetSortedItems()
    {
        $qty = $this->prepareQuoteItems();

        /** @var AbstractRule|MockObject $action */
        $action = $this->getMockBuilder(AbstractRule::class)
            ->disableOriginalConstructor()
            ->setMethods(['validateItems'])
            ->getMockForAbstractClass();
        $this->setProperty($action, 'validator', $this->initValidator());
        $action->expects($this->any())->method('validateItems')->will($this->returnValue($this->items));

        $items = $this->invokeMethod(
            $action,
            'getSortedItems',
            [
                $this->address,
                $this->initRule(),
                AbstractRule::DESC_SORT
            ]
        );

        $this->assertEquals(count($items), $qty, 'Items split failed.');
        $this->assertTrue($this->checkItemsIsSorted($items, AbstractRule::DESC_SORT), 'Items is not sorted.');
    }

    /**
     * @throws \ReflectionException
     */
    protected function initQuote()
    {
        $this->quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->address = $this->createMock(\Magento\Quote\Model\Quote\Address::class);

        $this->quote->expects($this->any())->method('getBillingAddress')->will($this->returnValue($this->address));
        $this->quote->expects($this->any())->method('getAllItems')->willReturnCallback(
            function () {
                return $this->items;
            }
        );

        $this->address->expects($this->any())->method('getAllItems')->willReturnCallback(
            function () {
                return $this->items;
            }
        );
    }

    /**
     * @param bool $mock
     *
     * @return Item|AbstractItem|MockObject
     * @throws \ReflectionException
     */
    protected function initQuoteItem($mock = true)
    {
        /** @var AbstractItem|MockObject $item */
        if ($mock) {
            $item = $this->createMock(AbstractItem::class);

            $item->expects($this->any())->method('getQuote')->will($this->returnValue($this->quote));
            $item->expects($this->any())->method('getAddress')->will($this->returnValue($this->address));
        } else {
            $item = $this->objectManager->getObject(Item::class);
            $item->setQuote($this->quote);
        }

        return $item;
    }

    /**
     * @param object $object
     * @param string $methodName
     * @param array $parameters
     *
     * @return mixed
     * @throws \ReflectionException
     */
    protected function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @param object $object
     * @param string $propertyName
     * @param mixed $value
     *
     * @return object
     * @throws \ReflectionException
     */
    protected function setProperty($object, $propertyName, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);

        return $object;
    }

    /**
     * @param bool $randomQty
     *
     * @return float|int
     * @throws \ReflectionException
     */
    protected function prepareQuoteItems($randomQty = true)
    {
        $itemId = 1;
        $totalQty = 0;
        $totalAmount = 0;

        while ($itemId <= static::ITEMS_COUNT) {
            $item = $this->initQuoteItem(false);
            $qty = $randomQty ? rand(1, 10) : 1;
            $item->setId($itemId)
                ->setBaseCalculationPrice($itemId * 10)
                ->setAmrulesId($itemId)->setData(Item::KEY_QTY, $qty)
                ->setProductId($itemId);
            $this->items[$itemId] = $item;
            $totalAmount += $itemId * 10;
            $totalQty += $qty;
            $itemId++;
        }

        return $randomQty ? $totalQty : $totalAmount;
    }

    /**
     * @return \Magento\SalesRule\Model\Validator|MockObject
     * @throws \ReflectionException
     */
    protected function initValidator()
    {
        /** @var \Magento\SalesRule\Model\Validator|MockObject $validator */
        $validator = $this->createPartialMock(\Magento\SalesRule\Model\Validator::class, ['getItemBasePrice']);
        $validator->expects($this->any())->method('getItemBasePrice')->willReturnCallback(
            function ($item) {
                return $item->getBaseCalculationPrice();
            }
        );

        return $validator;
    }

    /**
     * @return Rule|MockObject
     * @throws \ReflectionException
     */
    protected function initRule($mock = true)
    {
        if ($mock) {
            /** @var Rule|MockObject $rule */
            $rule = $this->createPartialMock(
                Rule::class,
                ['getDiscountStep', 'getSimpleAction', 'getDiscountQty']
            );

            $rule->expects($this->any())->method('getDiscountStep')->will($this->returnValue(static::RULE_DISCOUNT_STEP));
            $rule->expects($this->any())->method('getSimpleAction')->will($this->returnValue(static::RULE_SIMPLE_ACTION));
            $rule->expects($this->any())->method('getDiscountQty')->will($this->returnValue(static::RULE_DISCOUNT_QTY));
        } else {
            $rule = $this->objectManager->getObject(\Magento\SalesRule\Model\Rule::class);
        }

        return $rule;
    }


    /**
     * @return \Amasty\Rules\Helper\Product|MockObject
     * @throws \ReflectionException
     */
    protected function initProductHelper()
    {
        /** @var \Amasty\Rules\Helper\Product|MockObject $productHelper */
        $productHelper = $this->createMock(\Amasty\Rules\Helper\Product::class);
        $productHelper->expects($this->any())
            ->method('getItemPrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );
        $productHelper->expects($this->any())
            ->method('getItemBasePrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );
        $productHelper->expects($this->any())
            ->method('getItemOriginalPrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );
        $productHelper->expects($this->any())
            ->method('getItemBaseOriginalPrice')
            ->willReturnCallback(
                function ($item) {
                    return $item->getBaseCalculationPrice();
                }
            );

        return $productHelper;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule\Action\Discount\Data|MockObject $data
     *
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory|MockObject
     * @throws \ReflectionException
     */
    protected function initDiscountDataFactory($data = null)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory|MockObject $dataFactory */
        $dataFactory = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory::class,
            ['create']
        );
        if ($data) {
            $dataFactory->expects($this->any())->method('create')->will($this->returnValue($data));
        } else {
            $dataFactory->expects($this->any())
                ->method('create')
                ->willReturnCallback(
                    function () {
                        return $this->objectManager->getObject(\Magento\SalesRule\Model\Rule\Action\Discount\Data::class, []);
                    }
                );
        }

        return $dataFactory;
    }

    /**
     * @return \Magento\Framework\Pricing\PriceCurrencyInterface|MockObject
     */
    protected function initPriceCurrency()
    {
        $priceCurrency = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)
            ->getMock();
        $priceCurrency->expects($this->any())->method('convert')->will($this->returnArgument(0));
        $priceCurrency->expects($this->any())->method('round')->will($this->returnArgument(0));

        return $priceCurrency;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item[] $items
     * @param string $order
     *
     * @return bool
     */
    protected function checkItemsIsSorted($items, $order = AbstractRule::ASC_SORT)
    {
        if ($order === AbstractRule::DESC_SORT) {
            $items = array_reverse($items);
        }

        $prevValue = current($items);

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            if ($item->getBaseCalculationPrice() >= $prevValue->getBaseCalculationPrice()) {
                $prevValue = $item;
                continue;
            }

            return false;
        }

        return true;
    }
}
