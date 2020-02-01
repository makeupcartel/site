<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

namespace Amasty\Promo\Controller\Adminhtml\Banner;

/**
 * Class Label
 *
 * @author Artem Brunevski
 */

class Label extends AbstractUpload
{
    /**
     * @return string
     */
    public function getFileId()
    {
        return 'ampromorule_label_image';
    }
}