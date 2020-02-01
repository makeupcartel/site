<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Block\Post;

use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Block\Post\RelatedProduct;
use Aheadworks\Blog\Model\Source\Config\Related\BlockLayout;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Render as PricingRender;
use Aheadworks\Blog\Api\Data\PostInterface;

/**
 * Test for \Aheadworks\Blog\Block\Post\RelatedProduct
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RelatedProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RelatedProduct
     */
    private $block;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var ProductRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productRepositoryMock;

    /**
     * @var PostHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $postHelperMock;

    /**
     * @var ImageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageBuilderMock;

    /**
     * @var CartHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cartHelperMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->configMock = $this->getMockBuilder(Config::class)
            ->setMethods(['getRelatedProductsLimit', 'getRelatedBlockLayout', 'isRelatedDisplayAddToCart'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRepositoryMock = $this->getMockForAbstractClass(ProductRepositoryInterface::class);
        $this->postHelperMock = $this->getMockBuilder(PostHelper::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageBuilderMock = $this->getMockBuilder(ImageBuilder::class)
            ->setMethods(['setProduct', 'setImageId', 'setAttributes', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartHelperMock = $this->getMockBuilder(CartHelper::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlEncoderMock = $this->getMockBuilder(EncoderInterface::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = $objectManager->getObject(
            RelatedProduct::class,
            [
                'config' => $this->configMock,
                'productRepository' => $this->productRepositoryMock,
                'postHelper' => $this->postHelperMock,
                'imageBuilder' => $this->imageBuilderMock,
                'cartHelper' => $this->cartHelperMock,
                'urlEncoder' => $this->urlEncoderMock
            ]
        );
    }

    /**
     * Testing of getPostDataHelper method
     */
    public function testGetPostDataHelper()
    {
        $this->assertSame($this->postHelperMock, $this->block->getPostDataHelper());
    }

    /**
     * Testing of getProductById method
     */
    public function testGetProductById()
    {
        $productId = 1;

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $this->productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($productMock);
        $this->assertSame($productMock, $this->block->getProductById($productId));
    }

    /**
     * Testing of getImage method on return true
     */
    public function testGetImage()
    {
        $imageId = 'product_base_image';
        $productMock = $this->getMockBuilder(Product::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $productImageMock = $this->getMockBuilder(Image::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $this->imageBuilderMock->expects($this->once())
            ->method('setProduct')
            ->with($productMock)
            ->willReturnSelf();
        $this->imageBuilderMock->expects($this->once())
            ->method('setImageId')
            ->with($imageId)
            ->willReturnSelf();
        $this->imageBuilderMock->expects($this->once())
            ->method('setAttributes')
            ->willReturnSelf();
        $this->imageBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($productImageMock);

        $this->assertSame($productImageMock, $this->block->getImage($productMock, $imageId));
    }

    /**
     * Testing of getProductPrice method on return true
     */
    public function testGetProductPrice()
    {
        $priceRenderHtml = 'product price html code';
        $productMock = $this->getMockBuilder(Product::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        $blockMock = $this->getMockBuilder(PricingRender::class)
            ->setMethods(['render'])
            ->disableOriginalConstructor()
            ->getMock();
        $blockMock->expects($this->once())
            ->method('render')
            ->willReturn($priceRenderHtml);
        $layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('product.price.render.default')
            ->willReturn($blockMock);
        $this->block->setLayout($layoutMock);

        $this->assertEquals($priceRenderHtml, $this->block->getProductPrice($productMock));
    }

    /**
     * Testing of getDataMageInit method
     *
     * @param string $blockLayout
     * @param string $expected
     * @dataProvider getDataMageInitDataProvider
     */
    public function testGetDataMageInit($blockLayout, $expected)
    {
        $this->configMock->expects($this->once())
            ->method('getRelatedBlockLayout')
            ->willReturn($blockLayout);

        $this->assertEquals($expected, $this->block->getDataMageInit());
    }

    /**
     * Data provider for testGetDataMageInit method
     *
     * @return array
     */
    public function getDataMageInitDataProvider()
    {
        return [
            [BlockLayout::SLIDER_VALUE, '{"awBlogRelatedSlider": {}}'],
            [BlockLayout::SINGLE_ROW_VALUE, '{"awBlogRelatedGrid": {}}'],
            [BlockLayout::MULTIPLE_ROWS_VALUE, '{}'],
            ['default value', '{}']
        ];
    }

    /**
     * Testing of getRelatedProductIds method
     */
    public function testGetRelatedProductIds()
    {
        $relatedProductsLimit = 3;
        $relatedProductIds = [1, 2, 3, 4, 5];

        $postMock = $this->getMockForAbstractClass(PostInterface::class);
        $postMock->expects($this->once())
            ->method('getRelatedProductIds')
            ->willReturn($relatedProductIds);
        $this->block->setPost($postMock);
        $this->configMock->expects($this->once())
            ->method('getRelatedProductsLimit')
            ->willReturn($relatedProductsLimit);

        $this->assertEquals($relatedProductsLimit, count($this->block->getRelatedProductIds()));
    }

    /**
     * Testing of getBlockLayout method
     *
     * @param string $expected
     * @dataProvider getBlockLayoutDataProvider
     */
    public function testGetBlockLayout($expected)
    {
        $this->configMock->expects($this->once())
            ->method('getRelatedBlockLayout')
            ->willReturn($expected);

        $this->assertEquals($expected, $this->block->getBlockLayout());
    }

    /**
     * Data provider for testGetBlockLayout method
     *
     * @return array
     */
    public function getBlockLayoutDataProvider()
    {
        return [
            [BlockLayout::SLIDER_VALUE],
            [BlockLayout::SINGLE_ROW_VALUE],
            [BlockLayout::MULTIPLE_ROWS_VALUE]
        ];
    }

    /**
     * Testing of getBlockLayout method
     *
     * @param string $expected
     * @dataProvider isDisplayAddToCartDataProvider
     */
    public function testIsDisplayAddToCart($expected)
    {
        $this->configMock->expects($this->once())
            ->method('isRelatedDisplayAddToCart')
            ->willReturn($expected);

        $this->assertEquals($expected, $this->block->isDisplayAddToCart());
    }

    /**
     * Data provider for testIsDisplayAddToCart method
     *
     * @return array
     */
    public function isDisplayAddToCartDataProvider()
    {
        return [
            [1],
            [0]
        ];
    }

    /**
     * Testing of hasProductUrl method
     *
     * @param bool $value
     * @param bool $expected
     * @dataProvider hasProductUrlDataProvider
     */
    public function testHasProductUrl($value, $expected)
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->setMethods(['getVisibleInSiteVisibilities'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getVisibleInSiteVisibilities')
            ->willReturn($value);

        $class = new \ReflectionClass($this->block);
        $method = $class->getMethod('hasProductUrl');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->block, $productMock));
    }

    /**
     * Data provider for testHasProductUrl method
     *
     * @return array
     */
    public function hasProductUrlDataProvider()
    {
        return [
            [false, false],
            [true, true]
        ];
    }
}
