<?php

namespace Unirgy\RapidFlow\Model\Product;

use \Magento\Catalog\Model\Product\Image\Cache as BaseImageCache;
use \Magento\Catalog\Model\Product;
use Magento\Framework\App\Area;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Framework\View\ConfigInterface;

class ImageCache extends BaseImageCache
{
    /**
     * @var \Unirgy\RapidFlow\Helper\ImageHelper
     */
    protected $imageHelper;

    /**
     * @var \Unirgy\RapidFlow\Model\ResourceModel\ThemeCollection
     */
    protected $themeCollection;

    public function flushProduct(Product $product)
    {
        $galleryImages = $product->getMediaGalleryImages();
        if ($galleryImages) {
            $urfExtraData = $this->getUrfExtraData();
            $urfExtraDataIds = [];
            foreach ($urfExtraData as $__edIdx=>$__edData) {
                $urfExtraDataIds[$__edData['id']] = $__edIdx;
            }
            $allData = [
                $this->getData(),
                $this->getAdminData()
            ];
            foreach ($galleryImages as $image) {
                foreach ($allData as $currentData) {
                    foreach ($currentData as $imageData) {
                        $this->flushProductImageData($product, $imageData, $image->getFile());
                        if (array_key_exists($imageData['id'], $urfExtraDataIds)) {
                            $__idx = $urfExtraDataIds[$imageData['id']];
                            $__extraData = array_merge(
                                $urfExtraData[$__idx],
                                $imageData
                            );
                            $this->flushProductImageData($product, $__extraData, $image->getFile());
                        }
                    }
                }
            }
        }
        return $this;
    }
    public function getUrfExtraData()
    {
        return [
            [
                "id" => "product_page_image_medium",
                "constrain" => true,
                "aspect_ratio" => true,
                "frame" => false
            ],
            [
                "id" => "product_page_image_large",
                "constrain" => true,
                "aspect_ratio" => true,
                "frame" => false
            ],
        ];
    }
    public function flushProductImageData(Product $product, array $imageData, $file)
    {
        $this->imageHelper->init($product, $imageData['id'], $imageData);
        $this->imageHelper->setImageFile($file);

        if (isset($imageData['aspect_ratio'])) {
            $this->imageHelper->keepAspectRatio($imageData['aspect_ratio']);
        }
        if (isset($imageData['frame'])) {
            $this->imageHelper->keepFrame($imageData['frame']);
        }
        if (isset($imageData['transparency'])) {
            $this->imageHelper->keepTransparency($imageData['transparency']);
        }
        if (isset($imageData['constrain'])) {
            $this->imageHelper->constrainOnly($imageData['constrain']);
        }
        if (isset($imageData['background'])) {
            $this->imageHelper->backgroundColor($imageData['background']);
        }

        $this->imageHelper->urfInitBaseFile();
        $this->imageHelper->urfFlushCache();

        return $this;
    }
    protected $adminData = [];
    protected function getAdminData()
    {
        if (!$this->adminData) {
            /** @var \Magento\Theme\Model\Theme $theme */
            $themeCollection = $this->themeCollection->urfLoadAdminRegisteredThemes();
            foreach ($themeCollection as $theme) {
                $config = $this->viewConfig->getViewConfig([
                    'area' => Area::AREA_ADMINHTML,
                    'themeModel' => $theme,
                ]);
                $images = $config->getMediaEntities('Magento_Catalog', ImageHelper::MEDIA_TYPE_CONFIG_NODE);
                foreach ($images as $imageId => $imageData) {
                    $this->adminData[$theme->getCode() . $imageId] = array_merge(['id' => $imageId], $imageData);
                }
            }
        }
        return $this->adminData;
    }
}