<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Controller\Adminhtml\Banner;

/**
 * Class Afterproduct
 *
 * @author Artem Brunevski
 */


class Afterproduct extends AbstractUpload
{
    /**
     * @return string
     */
    public function getFileId()
    {
        return 'ampromorule_after_product_banner_image';
    }
}