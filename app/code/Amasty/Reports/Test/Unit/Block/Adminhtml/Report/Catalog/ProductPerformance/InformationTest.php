<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Test\Unit\Block\Adminhtml\Report\Catalog\ProductPerformance;

use Amasty\Reports\Block\Adminhtml\Report\Catalog\ProductPerformance\Information;
use Amasty\Reports\Test\Unit\Traits;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class InformationTest
 *
 * @see Information
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class InformationTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @covers Information::getProduct
     * @throws \ReflectionException
     */
    public function testGetProduct()
    {
        $block = $this->createPartialMock(Information::class, ['prepareProductData', 'getRequest']);
        $request = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $productRepository = $this->createMock(\Magento\Catalog\Model\ProductRepository::class);
        $dataObjectFactory = $this->createMock(\Magento\Framework\DataObjectFactory::class);

        $this->setProperty($block, 'productRepository', $productRepository, Information::class);
        $this->setProperty($block, 'dataObjectFactory', $dataObjectFactory, Information::class);

        $request->expects($this->any())->method('getParam')
            ->willReturnOnConsecutiveCalls([], ['sku' => 'test'], ['sku' => 'test']);
        $productRepository->expects($this->any())->method('get')
            ->willReturnOnConsecutiveCalls([], $this->throwException(new NoSuchEntityException(__('test'))));
        $dataObjectFactory->expects($this->any())->method('create')->willReturn(new \Magento\Framework\DataObject());
        $block->expects($this->any())->method('getRequest')->willReturn($request);
        $block->expects($this->once())->method('prepareProductData');

        $this->assertNull($block->getProduct()->getError());
        $block->getProduct();
        $this->assertNotNull($block->getProduct()->getError());
    }

    /**
     * @covers Information::prepareProductData
     * @throws \ReflectionException
     */
    public function testPrepareProductData()
    {
        $reportCollection = $this->getMockBuilder(\Magento\Reports\Model\ResourceModel\Product\Collection::class)
            ->setMethods([
                'setProductAttributeSetId', 'addViewsCount', 'getData', 'addFieldToFilter', 'getSelect', 'getFirstItem'
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $collection = $this->getMockBuilder(\Amasty\Reports\Model\ResourceModel\Catalog\ProductPerformance\CollectionFactory::class)
            ->setMethods(['create', 'getOrderInfo', 'getQty', 'getRevenue'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $imageHelper = $this->createMock(\Magento\Catalog\Helper\Image::class);
        $typeInstance = $this->createMock(\Magento\Catalog\Model\Product\Type\AbstractType::class);
        $select = $this->getMockBuilder(\Zend_Db_Select::class)
            ->setMethods(['where'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $object = $this->getObjectManager()->getObject(DataObject::class);
        $product = $this->getObjectManager()->getObject(\Magento\Catalog\Model\Product::class);

        $select->expects($this->any())->method('where')->willReturn($select);
        $reportCollection->expects($this->once())->method('setProductAttributeSetId')->willReturn($reportCollection);
        $reportCollection->expects($this->once())->method('addViewsCount')->willReturn($reportCollection);
        $reportCollection->expects($this->once())->method('addFieldToFilter')->willReturn($reportCollection);
        $reportCollection->expects($this->once())->method('getSelect')->willReturn($select);
        $reportCollection->expects($this->once())->method('getFirstItem')->willReturn(new DataObject(
            [
                'data' => ['entity_id' => 5, 'views' => 3]
            ]
        ));
        $collection->expects($this->once())->method('create')->willReturn($collection);
        $collection->expects($this->once())->method('getOrderInfo')->willReturn($collection);
        $imageHelper->expects($this->once())->method('init')->willReturn($imageHelper);

        $block = $this->getObjectManager()->getObject(
            Information::class,
            [
                'reportCollection' => $reportCollection,
                'collection' => $collection,
                'imageHelper' => $imageHelper,
            ]
        );

        $this->setProperty($product, '_calculatePrice', false, \Magento\Catalog\Model\Product::class);
        $this->setProperty($product, '_typeInstance', $typeInstance, \Magento\Catalog\Model\Product::class);

        $product->setId(5);
        $product->setName('test');
        $product->setPrice(10);
        $this->invokeMethod($block, 'prepareProductData', [$object, $product, ['from' => 1, 'to' => 2]]);
        $this->assertEquals('test', $object->getName());
    }
}
