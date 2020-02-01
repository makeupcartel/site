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

namespace Magedelight\Megamenu\Model\Menu\Source;

use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Store\Model\StoreRepository;

/**
 * Class MenuList
 *
 * @package Magedelight\Megamenu\Model\Menu\Source
 */
class MenuList implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magedelight\Megamenu\Model\Menu
     */
    protected $megamenuMenu;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param \Magedelight\Megamenu\Model\Menu $megamenuMenu
     */
    public function __construct(\Magedelight\Megamenu\Model\Menu $megamenuMenu, Http $request, StoreManagerInterface $storeManager, StoreRepository $storeRepository)
    {
        $this->megamenuMenu = $megamenuMenu;
        $this->request = $request;
        $this->_storeManager = $storeManager;
        $this->_storeRepository = $storeRepository;
    }

    /**
     * @param \Magedelight\Megamenu\Model\Menu $megamenuMenu
     * @return string
     */
    public function getStores()
    {

        $store = $this->request->getParam('store');
        $website = $this->request->getParam('website');
        $current = $this->request->getParam('section');
        
        $allStores = [];
        if (isset($store) and ! empty($store)) {
            $allStores[] = $store;
        } elseif (isset($website) and ! empty($website)) {
            $website = $this->_storeManager->getWebsite($website);
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $allStores[] = $store->getStoreId();
                }
            }
        } else {
            //$allStores[] = 0;
            $stores = $this->_storeRepository->getList();
            foreach ($stores as $store) {
                $allStores[] = $store["store_id"];
            }
        }
        $allStores[] = 0;
        return $allStores;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        $storeId = $this->getStores();

        $menus = $this->megamenuMenu->getCollection();

        $menus->getSelect()->join(
            ['u' => $menus->getTable('megamenu_menus_store')],
            'u.menu_id = main_table.menu_id',
            ['u.store_id']
        );

        $menus->addFieldToFilter(
            'u.store_id',
            [
                    ['in' => $storeId],
                ]
        );

        $sourceArray = [];
        $count = 0;
        if (isset($menus) and ! empty($menus)) {
            foreach ($menus as $menu) {
                $sourceArray[$count]['value'] = $menu->getMenuId();
                $sourceArray[$count]['label'] = $menu->getMenuName();
                $count++;
            }
        }

        return $sourceArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('No'), 1 => __('Yes')];
    }
}
