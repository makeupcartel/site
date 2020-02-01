<?php

namespace Unirgy\RapidFlow\Helper;

use \Magento\Catalog\Helper\Image as BaseImageHelper;
use \Magento\Catalog\Model\Product;

class ImageHelper extends BaseImageHelper
{
    public function urfInitBaseFile()
    {
        $this->initBaseFile();
    }
    public function urfFlushCache()
    {
        /** @var \Unirgy\RapidFlow\Model\Product\Image $model */
        $model = $this->_getModel();
        if (!$model->isBaseFilePlaceholder()
            && $model->urfGetFilepath() !== true
            && $model->isCached()
        ) {
            $model->urfRemoveFile($model->urfGetFilepath());
        }
    }
}