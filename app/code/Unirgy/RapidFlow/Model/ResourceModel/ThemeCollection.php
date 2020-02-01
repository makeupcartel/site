<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Model\ResourceModel;

class ThemeCollection extends \Magento\Theme\Model\ResourceModel\Theme\Collection
{
    public function urfLoadAdminRegisteredThemes()
    {
        $this->_reset()->clear();
        return $this->setOrder('theme_title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->filterVisibleThemes()->addAreaFilter(\Magento\Framework\App\Area::AREA_ADMINHTML);
    }
}