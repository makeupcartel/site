<?php

namespace Unirgy\RapidFlow\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Unirgy\RapidFlow\Model\Profile;

class ImageCache
{
    protected $_productsToUpdate = [];
    /**
     * @var \Unirgy\RapidFlow\Model\Product\ImageCacheFactory
     */
    protected $_imageCacheFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;
    public function __construct(
        \Unirgy\RapidFlow\Model\Product\ImageCacheFactory $imageCacheFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    )
    {
        $this->_imageCacheFactory = $imageCacheFactory;
        $this->_productRepository = $productRepository;
    }

    public function addProductIdForFlushCache($productId, $productData = [])
    {
        $this->_productsToUpdate[$productId] = $productData;
    }
    public function flushProductsImageCache(Profile $profile)
    {
        try {
            $reflImage = new \ReflectionClass('\Magento\Catalog\Model\Product\Image');
            foreach ($reflImage->getProperties() as $__rip) {
                if ($__rip->getName()=='imageAsset') {
                    $__rip->setAccessible(true);
                    \Unirgy\RapidFlow\Model\Product\Image::$myImageAsset = $__rip;
                }
            }
            foreach ($this->_productsToUpdate as $productId => $productData) {
                try {
                    /** @var Product $product */
                    $product = $this->_productRepository->getById($productId);
                } catch (NoSuchEntityException $e) {
                    continue;
                }

                /** @var \Unirgy\RapidFlow\Model\Product\ImageCache $imageCache */
                $imageCache = $this->_imageCacheFactory->create();
                $imageCache->flushProduct($product);

            }
        } catch (\Exception $e) {
            $profile->getLogger()->warning($e->getMessage());
        }
    }
}