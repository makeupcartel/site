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

namespace Magedelight\Megamenu\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 *
 * @package Magedelight\Megamenu\Controller\Adminhtml\Menu
 */
class Save extends \Magento\Backend\App\Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magedelight_Megamenu::save';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magedelight\Megamenu\Model\Menu
     */
    protected $menuModel;

    /**
     * @var \Magedelight\Megamenu\Model\MenuItems
     */
    protected $menuItemModel;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        \Magedelight\Megamenu\Model\Menu $menuModel,
        \Magedelight\Megamenu\Model\MenuItems $menuItemModel
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        $this->menuModel = $menuModel;
        $this->menuItemModel = $menuItemModel;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $menuData = json_decode($data['menu_data_json'], true);
            if (count($menuData) > 0) {
                $menuDataFinal = $menuData['menu_data'];
            }
            $data = $this->dataProcessor->filter($data);


            if (isset($data['is_active']) && !empty($data['is_active'])) {
                $data['is_active'] = 1;
            } else {
                $data['is_active'] = 0;
            }

            if (empty($data['menu_id'])) {
                $data['menu_id'] = null;
            }

            /** @var \Magedelight\Megamenu\Model\Menu $model */
            $model = $this->menuModel;

            $id = $this->getRequest()->getParam('menu_id');
            if ($id) {
                $model->load($id);
            }

            if ($data['customer_groups']) {
                $data['customer_groups'] = implode(',', $data['customer_groups']);
            }

            $model->setData($data);
            $this->_eventManager->dispatch(
                'megamenu_menu_prepare_save',
                ['menu' => $model, 'request' => $this->getRequest()]
            );

            if (!$this->dataProcessor->validateRequireEntry($data)) {
                return $resultRedirect->setPath('*/*/edit', ['menu_id' => $model->getMenuId(), '_current' => true]);
            }

            try {
                $form = $model->save();

                $menuId = $form->getMenuId();
                $deleteItems = $this->menuItemModel->deleteItems($menuId);

                if (count($menuData) > 0) {
                    //echo "<pre>";
                    //print_r($menuDataFinal);
//                    die('test');
                    foreach ($menuDataFinal as $i => $menu_item_data) {
                        $menu_data = $menuDataFinal[$i];
                        if (isset($menu_data['item_name']) && isset($menu_data['item_type'])) {
                            $itemsData = [];
                            $itemsData['item_name'] = $menu_data['item_name'];
                            $itemsData['item_type'] = $menu_data['item_type'];
                            $itemsData['sort_order'] = $menu_data['sort_order'];
                            $itemsData['item_parent_id'] = $menu_data['item_parent_id'];
                            $itemsData['menu_id'] = $menuId;
                            $itemsData['object_id'] = $menu_data['object_id'];
                            $itemsData['item_link'] = $menu_data['item_link'];
                            $itemsData['item_font_icon'] = $menu_data['item_font_icon'];
                            $itemsData['item_class'] = $menu_data['item_class'];
                            $itemsData['animation_option'] = $menu_data['animation_option'];

                            if ($menu_data['item_all_cat']) {
                                $itemsData['category_display'] = $menu_data['item_all_cat'];
                            }
                            if ($menu_data['item_vertical_menu']) {
                                $itemsData['category_vertical_menu'] = $menu_data['item_vertical_menu'];
                            }

                            if ($menu_data['vertical_menu_bgcolor']) {
                                $itemsData['category_vertical_menu_bg'] = $menu_data['vertical_menu_bgcolor'];
                            }


                            if (!empty($menu_data['item_columns'])) {
                                $itemsData['item_columns'] = json_encode($menu_data['item_columns']);
                            }

                            if (!empty($menu_data['category_columns'])) {
                                $itemsData['category_columns'] = json_encode($menu_data['category_columns']);
                            }

                            $currentItem = $this->menuItemModel->setData($itemsData)->save();
                            $itemId = $currentItem->getItemId();
                            foreach ($menuDataFinal as $key => $val) {
                                if (isset($val['item_parent_id']) && $val['item_parent_id'] == $i) {
                                    $menuDataFinal[$key]['item_parent_id'] = $itemId;
                                }
                            }
                        }
                    }
                }
                $this->messageManager->addSuccess(__('You saved the menu.'));
                $this->dataPersistor->clear('megamenu_menu');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['menu_id' => $model->getMenuId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, $e->getMessage());
            }

            $this->dataPersistor->set('megamenu_menu', $data);
            return $resultRedirect->setPath('*/*/edit', ['menu_id' => $this->getRequest()->getParam('menu_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
