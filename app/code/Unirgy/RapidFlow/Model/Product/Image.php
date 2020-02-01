<?php

namespace Unirgy\RapidFlow\Model\Product;

use \Magento\Catalog\Model\Product\Image as BaseImage;
use \Magento\Catalog\Model\Product;

class Image extends BaseImage
{
    public function urfRemoveFile($filename)
    {
        if ($this->_mediaDirectory->isFile($filename)) {
            $this->_mediaDirectory->delete($filename);
        }
    }
    public function urfGetFilepath()
    {
        if (isset(self::$myImageAsset)) {
            return self::$myImageAsset->getValue($this)->getPath();
        } else {
            return $this->getNewFile();
        }
    }
    public static $myImageAsset;
}