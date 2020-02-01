<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

/**
 * Class Top
 *
 * @author Artem Brunevski
 */

namespace Amasty\Promo\Controller\Adminhtml\Banner;


class Top extends AbstractUpload
{
    /**
     * @return string
     */
    public function getFileId()
    {
        return 'ampromorule_top_banner_image';
    }
}