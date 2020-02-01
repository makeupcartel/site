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

namespace Magedelight\Megamenu\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Edit
 *
 * @package Magedelight\Megamenu\Model\ResourceModel
 */
class Menu extends AbstractDb
{

    /**
     * @var \Magedelight\Megamenu\Model\MenuItems
     */
    protected $menuItemModel;

    public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context, \Magedelight\Megamenu\Model\MenuItems $menuItemModel, $connectionName = null)
    {
        parent::__construct($context, $connectionName);
        $this->menuItemModel = $menuItemModel;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('megamenu_menus', 'menu_id');
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $menuId
     * @return array
     */
    public function lookupStoreIds($menuId)
    {
        $connection = $this->getConnection();

        $linkField = 'menu_id';

        $select = $connection->select()
                ->from(['mms' => $this->getTable('megamenu_menus_store')], 'store_id')
                ->join(
                    ['mm' => $this->getMainTable()],
                    'mms.' . $linkField . ' = mm.' . $linkField,
                    []
                )
                ->where('mm.menu_id = :menu_id');

        return $connection->fetchCol($select, ['menu_id' => (int) $menuId]);
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _afterSave(AbstractModel $object)
    {
        $newStores = $object->getData('store_id');
        if (!empty($newStores)) {
            $linkField = 'menu_id';
            $menu_id = $object->getData('menu_id');
            $connection = $this->getConnection();
            $oldStores = $this->lookupStoreIds($menu_id);
            $table = $this->getTable('megamenu_menus_store');
            $delete = array_diff($oldStores, $newStores);

            if ($delete) {
                $where = [
                    $linkField . ' = ?' => (int) $menu_id,
                    'store_id IN (?)' => $delete,
                ];
                $connection->delete($table, $where);
            }

            $insert = array_diff($newStores, $oldStores);
            if ($insert) {
                $data = [];
                foreach ($insert as $storeId) {
                    $data[] = [
                        $linkField => (int) $menu_id,
                        'store_id' => (int) $storeId
                    ];
                }
                $connection->insertMultiple($table, $data);
            }
        }
        return parent::_afterSave($object);
    }

    /**
     * Perform actions after object load
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _afterLoad(AbstractModel $object)
    {
        if ($object->getData('menu_id')) {
            $stores = $this->lookupStoreIds((int) $object->getData('menu_id'));
            $object->setData('store_id', $stores);
        }
        return $object;
    }

    /**
     * Delete the object
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function delete(\Magento\Framework\Model\AbstractModel $object)
    {
        $menuItemsModel = $this->menuItemModel
                ->getCollection()
                ->addFieldToFilter('menu_id', $object->getId())
                ->walk('delete');

        parent::delete($object);
    }
}
