<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Test\Unit\Helper;

use Amasty\Promo\Helper\Data;
use Amasty\Promo\Test\Unit\Traits;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class DataTest
 *
 * @see Data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers       Data::getPromoItemsDataArray
     * @dataProvider prepareData
     *
     * @param array $itemsData
     * @param int $productQty
     * @param array $expected
     *
     * @throws \ReflectionException
     */
    public function testGetPromoItemsDataArray($itemsData, $productQty, $expected)
    {
        $promoRegistry = $this->createMock(\Amasty\Promo\Model\Registry::class);
        $promoRegistry->expects($this->once())->method('updatePromoItemsReservedQty');

        $product = $this->createMock(\Amasty\Promo\Model\Product::class);
        $product->expects($this->any())->method('getProductQty')->willReturn($productQty);

        $items = [];

        foreach ($itemsData as $itemData) {
            $items[] = $this->initItem($itemData);
        }

        $model = $this->getObjectManager()->getObject(
            Data::class,
            [
                'promoRegistry' => $promoRegistry,
                'promoItemRegistry' => $this->initPromoItemRegistry($items),
                'product' => $product
            ]
        );

        $result = $model->getPromoItemsDataArray();

        $this->assertArrayHasKey('common_qty', $result);
        $this->assertArrayHasKey('triggered_products', $result);
        $this->assertArrayHasKey('promo_sku', $result);
        $this->assertEquals($expected['common_qty'], $result['common_qty']);
        $this->assertEquals(\count($result['promo_sku']), \count($itemsData));
    }

    /**
     * @return array
     */
    public function prepareData()
    {
        return [
            [
                [
                    [
                        'sku' => 'ASQ-1',
                        'qty' => 1,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 13
                    ],
                    [
                        'sku' => 'ASQ-2',
                        'qty' => 2,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 10
                    ],
                    [
                        'sku' => 'ASQ-3',
                        'qty' => 3,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 15
                    ],
                    [
                        'sku' => 'ASQ-4',
                        'qty' => 4,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 11
                    ]
                ],
                10,
                [
                    'common_qty' => 4
                ]
            ],
            [
                [
                    [
                        'sku' => 'ASQ-1',
                        'qty' => 1,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 13
                    ],
                    [
                        'sku' => 'ASQ-2',
                        'qty' => 2,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 10
                    ],
                    [
                        'sku' => 'ASQ-3',
                        'qty' => 3,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 15
                    ],
                    [
                        'sku' => 'ASQ-4',
                        'qty' => 4,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 11
                    ]
                ],
                10,
                [
                    'common_qty' => 10
                ]
            ],
            [
                [
                    [
                        'sku' => 'ASQ-1',
                        'qty' => 1,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 13
                    ],
                    [
                        'sku' => 'ASQ-2',
                        'qty' => 2,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ALL,
                        'minimal_price' => 10
                    ],
                    [
                        'sku' => 'ASQ-3',
                        'qty' => 3,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 15
                    ],
                    [
                        'sku' => 'ASQ-4',
                        'qty' => 4,
                        'rule_type' => \Amasty\Promo\Model\Rule::RULE_TYPE_ONE,
                        'minimal_price' => 11
                    ]
                ],
                10,
                [
                    'common_qty' => 7
                ]
            ],
        ];
    }

    /**
     * @param \Amasty\Promo\Model\ItemRegistry\PromoItemData[] $items
     *
     * @return \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry|MockObject
     * @throws \ReflectionException
     */
    private function initPromoItemRegistry($items)
    {
        /** @var \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry|MockObject $promoItemRegistry */
        $promoItemRegistry = $this->createPartialMock(\Amasty\Promo\Model\ItemRegistry\PromoItemRegistry::class, []);
        $this->setProperty($promoItemRegistry, 'storage', $items, \Amasty\Promo\Model\ItemRegistry\PromoItemRegistry::class);

        return $promoItemRegistry;
    }

    /**
     * @param array $dataArray
     *
     * @return \Amasty\Promo\Model\ItemRegistry\PromoItemData|MockObject
     */
    private function initItem($dataArray)
    {
        /** @var \Amasty\Promo\Model\ItemRegistry\PromoItemData|MockObject $item */
        $item = $this->createPartialMock(\Amasty\Promo\Model\ItemRegistry\PromoItemData::class, ['getQtyToProcess']);
        $item->expects($this->exactly(2))->method('getQtyToProcess')->willReturn($dataArray['qty']);

        $item->setSku($dataArray['sku'])
            ->setRuleId(1)
            ->setRuleType($dataArray['rule_type'])
            ->setMinimalPrice($dataArray['minimal_price'])
            ->setDiscountItem('discountItem');

        return $item;
    }
}
