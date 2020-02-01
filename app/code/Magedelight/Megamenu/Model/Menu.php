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
 * Class Menu
 *
 * @package Magedelight\Megamenu\Model
 */
class Menu extends AbstractModel
{
    /**#@+
     * Menu's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**#@+
     * Menu's Types
     */
    const NORMAL_MENU = 1;
    const MEGA_MENU = 2;
    /**#@-*/

    
    /**
     * Megamenu menu cache tag
     */
    const CACHE_TAG = 'megamenu_menu';

    /**
     * @var string
     */
    protected $_cacheTag = 'megamenu_menu';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'megamenu_menu';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magedelight\Megamenu\Model\ResourceModel\Menu');
    }

   
    /**
     * Prepare menu's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Prepare menu's types.
     *
     * @return array
     */
    
    public function getAvailableTypes()
    {
        return [self::NORMAL_MENU => __('NORMAL MENU'), self::MEGA_MENU => __('MEGA MENU')];
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
}
