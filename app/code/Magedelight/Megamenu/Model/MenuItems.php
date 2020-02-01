<?php
/**
 * Magedelight
 * Copyright (C) 2017 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Megamenu
 * @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Megamenu\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class MenuItems
 *
 * @package Magedelight\Megamenu\Model
 */
class MenuItems extends AbstractModel
{
    
    /**
     * Megamenu menu cache tag
     */
    const CACHE_TAG = 'megamenu_menuitems';

    /**
     * @var string
     */
    protected $_cacheTag = 'megamenu_menuitems';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'megamenu_menuitems';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magedelight\Megamenu\Model\ResourceModel\MenuItems');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    
    public function deleteItems($menuId)
    {
        $getMenuItems = $this->getCollection()
                ->addFieldToFilter('menu_id', $menuId);
        foreach ($getMenuItems as $menuItem) {
            $menuItem->delete();
        }
    }
}
