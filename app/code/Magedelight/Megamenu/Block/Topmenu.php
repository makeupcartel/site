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

namespace Magedelight\Megamenu\Block;

use Magento\Theme\Block\Html\Topmenu as MagentoTopmenu;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\Data\Tree\NodeFactory;
use Magedelight\Megamenu\Model\MenuFactory;
use Magedelight\Megamenu\Model\MenuItemsFactory;
use Magento\Cms\Model\BlockFactory;

/**
 * Class Topmenu
 *
 * @package Magedelight\Megamenu\Block
 */
class Topmenu extends MagentoTopmenu
{

    /**
     * @var \Magedelight\Megamenu\Model\MenuFactory
     */
    protected $_menuFactory;

    /**
     * @var \Magedelight\Megamenu\Model\MenuItemsFactory
     */
    protected $_menuItemsFactory;

    /**
     * @var int
     */
    protected $_primaryMenuId;

    /**
     * @var \Magedelight\Megamenu\Model\Menu
     */
    protected $_primaryMenu;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Cms\Helper\Page
     */
    protected $pageHelper;

    /**
     * @var \Magento\Cms\Model\Page
     */
    public $pageModel;

    /**
     * @param Template\Context $context
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     * @param array $data
     * @param MenuFactory $menuFactory
     * @param MenuItemsFactory $menuItemsFactory
     * @param BlockFactory $blockFactory
     * @param \Magento\Cms\Model\Page $page
     */
    public function __construct(
        Template\Context $context,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        MenuFactory $menuFactory,
        MenuItemsFactory $menuItemsFactory,
        BlockFactory $blockFactory,
        \Magento\Cms\Model\Page $page,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Cms\Helper\Page $pageHelper,
        \Magento\Cms\Model\Page $pageModel,
        array $data = []
    ) {
        parent::__construct($context, $nodeFactory, $treeFactory, $data);
        $this->_menuFactory = $menuFactory;
        $this->_menuItemsFactory = $menuItemsFactory;
        $this->_scopeInterface = $context->getScopeConfig();
        $this->_blockFactory = $blockFactory;
        $this->_page = $page;
        $this->customerSession = $session;
        $this->registry = $registry;
        $this->categoryRepository = $categoryRepository;
        $this->pageHelper = $pageHelper;
        $this->pageModel = $pageModel;
        $this->storeManager = $context->getStoreManager();
    }

    /**
     * Get current category id
     */
    public function getCurentCat()
    {
        $category = $this->registry->registry('current_category'); //get current category
        if (isset($category) and ! empty($category->getId())) {
            return $category->getId();
        }
    }

    /**
     * Get current page id
     */
    public function getCurentPage()
    {
        if ($this->_page->getId()) {
            return $pageId = $this->_page->getId();
        }
    }

    /**
     * Set Template for menubased on its type
     *
     * @param string
     */
    public function setCustomTemplate($template)
    {
        $this->_primaryMenuId = $this->getStoreMenuId();
        $this->_primaryMenu = $this->_menuFactory->create()->load($this->_primaryMenuId);
        if (($this->_primaryMenu->getMenuType() == 2) and ( $this->_primaryMenu->getIsActive() == 1) and ( $this->getConfigMenuStatus() == 1)) {/* Set Megamenu Custom Template */
            $this->setTemplate('Magedelight_Megamenu::menu/topmenu.phtml');
        } else { /* Set Magento Custom Template */
            $this->setTemplate($template);
        }
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Retrieve Menu Id for the current store
     *
     * @return int
     */
    public function getConfigMenuStatus()
    {
        return $this->_scopeInterface->getValue(
            'magedelight/general/megamenu_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve Menu Id for the current store
     *
     * @return int
     */
    public function getStoreMenuId()
    {
        $menu_id = $this->_scopeInterface->getValue(
            'magedelight/general/primary_menu',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $currentCustomerGroupId = $this->customerSession->getCustomerGroupId();

        $menu = $this->_menuFactory->create()->load($menu_id);
        $customerGroupsArray = explode(',', trim($menu->getCustomerGroups()));
        if (!in_array($currentCustomerGroupId, $customerGroupsArray) || $menu->getIsActive() != 1) {
            $menu_id = '';
        }
        if (empty($menu_id)) {
            $current_store_id = $this->getStoreId();
            $menuCollection = $this->_menuFactory->create()->getCollection()
                    ->addStoreFilter($current_store_id)
                    ->addFieldToFilter('is_active', '1')
                    ->addFieldToFilter('customer_groups', ['finset' => $currentCustomerGroupId])
                    ->setPageSize(1)
                    ->setCurPage(1);
            foreach ($menuCollection as $singleCollection) {
                return $menu_id = $singleCollection->getMenuId();
            }
        }
        if (empty($menu_id)) {
            $menuCollection = $this->_menuFactory->create()->getCollection()
                    ->addStoreFilter(0)
                    ->addFieldToFilter('is_active', '1')
                    ->addFieldToFilter('customer_groups', ['finset' => $currentCustomerGroupId])
                    ->setPageSize(1)
                    ->setCurPage(1);
            foreach ($menuCollection as $singleCollection) {
                return $menu_id = $singleCollection->getMenuId();
            }
        }

        return $menu_id;
    }

    /**
     * Check item children
     *
     * @param int
     * @return int
     */
    protected function hasChildrenItems($parentId)
    {
        $count = $this->_menuItemsFactory->create()->getCollection()
                ->addFieldToFilter('menu_id', $this->_primaryMenuId)
                ->addFieldToFilter('item_parent_id', $parentId)
                ->count();
        return $count;
    }

    /**
     * Retrieve Inline menu Style for extra css
     *
     * @return string
     */
    public function menuStyleHtml()
    {
        if (!empty(trim($this->_primaryMenu->getMenuStyle()))) {
            return '<style>' . $this->_primaryMenu->getMenuStyle() . '</style>';
        }
        return '';
    }

    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_before',
            ['menu' => $this->_menu, 'block' => $this]
        );

        $this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);

        if (($this->_primaryMenu->getMenuType() == 2) and ( $this->_primaryMenu->getIsActive() == 1) and ( $this->getConfigMenuStatus() == 1)) {
            $menuItems = $this->_menuItemsFactory->create()->getCollection()
                    ->addFieldToFilter('menu_id', $this->_primaryMenuId)
                    ->addFieldToFilter('item_parent_id', 0)
                    ->setOrder('sort_order', 'ASC');
            $html = '';
            foreach ($menuItems as $item) {
                $childrenWrapClass = "level0 nav-1 first parent main-parent";
                $html .= $this->setMegamenu($item, $childrenWrapClass);
            }
        } elseif (($this->_primaryMenu->getMenuType() == 1) and ( $this->_primaryMenu->getIsActive() == 1) and ( $this->getConfigMenuStatus() == 1)) {
            $menuItems = $this->_menuItemsFactory->create()->getCollection()
                    ->addFieldToFilter('menu_id', $this->_primaryMenuId)
                    ->addFieldToFilter('item_parent_id', 0);
            $parent = 'root';
            $level = 0;
            $html = $this->setPrimaryMenu($menuItems, $level, $parent, $outermostClass);
        } else {
            $html = $this->_getHtml($this->_menu, $childrenWrapClass, $limit);
        }

        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
        $this->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_after',
            ['menu' => $this->_menu, 'transportObject' => $transportObject]
        );
        $html = $transportObject->getHtml();
        return $html;
    }

    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param array $menuItems
     * @param int $level
     * @param int $parent
     * @param string $outermostClass
     * @return string
     */
    public function setPrimaryMenu($menuItems, $level = 0, $parent = '', $outermostClass = '')
    {
        $html = '';
        $class = 'level0 level-top parent ui-menu-item';
        $linkClass = 'level-top ';
        if ($parent != 'root') {
            $html .= '<ul class="level' . $level . ' submenu">';
            $linkClass = '';
        }
        foreach ($menuItems as $menuItem) {
            $menuItemId = $menuItem->getItemId();
            $linkurl = $menuItem->getItemLink();
            $dataclass = $menuItem->getItemClass();

            if (!$linkurl) {
                $linkurl = $this->generateMenuUrl($menuItem);
            }

            $hasChildren = $this->hasChildrenItems($menuItemId);

            if ($hasChildren) {
                $class = 'level' . $level . ' parent';
            } else {
                $class = 'level' . $level;
            }

            if ($menuItem->getItemType() == 'category') {
                if ($menuItem->getObjectId() == $this->getCurentCat()) {
                    $class .= ' active';
                }
            } elseif ($menuItem->getItemType() == 'pages') {
                if ($menuItem->getObjectId() == $this->getCurentPage()) {
                    $class .= ' active';
                }
            }

            $html .= '<li class="' . $class . ' ' . $linkClass . ' ' . $dataclass . '">';

            if ($hasChildren) {
                $html .= '<a href="' . $linkurl . '" class="' . $linkClass . ' ui-corner-all"><span class="megaitemicons">' . $menuItem->getItemFontIcon() . '</span> <span>' . $this->escapeHtml($this->generateMenuName($menuItem)) . '</span></a>';
                $menuItems = $this->_menuItemsFactory->create()->getCollection()
                        ->addFieldToFilter('menu_id', $this->_primaryMenuId)
                        ->addFieldToFilter('item_parent_id', $menuItemId);
                //Get list of child menu
                $html .= $this->setPrimaryMenu($menuItems, $level + 1);
            } else {
                $html .= '<a href="' . $linkurl . '" class="' . $linkClass . ' ui-corner-all "><span class="megaitemicons">' . $menuItem->getItemFontIcon() . '</span> <span>' . $this->escapeHtml($this->generateMenuName($menuItem)) . '</span></a>';
            }

            $html .= '</li>';
        }
        if ($parent != 'root') {
            $html .= '</ul>';
        }
        return $html;
    }

    /**
     * Retrieve menu url based on there type
     *
     * @param MenuFactory $menuItem
     * @return string
     */
    public function generateMenuUrl($menuItem)
    {
        $linkurl = $menuItem->getItemLink();
        $url = '';
        /* if (!empty($linkurl)) {
          return $linkurl;
          } */
        if ($menuItem->getItemType() == "link" && !empty($linkurl)) {
            return $linkurl;
            //return $this->storeManager->getStore()->getBaseUrl() . $linkurl;
        }
        if ($menuItem->getItemType() == "category") {
            $url = $this->categoryRepository->get($menuItem->getObjectId())->getUrl();
        }
        if ($menuItem->getItemType() == "pages") {
            $url = $this->storeManager->getStore()->getBaseUrl() . $menuItem->getItemLink();
            //$url = $this->pageHelper->getPageUrl($menuItem->getObjectId());
        }
        return $url;
    }

    /**
     * Retrieve menu name based on there type
     *
     * @param MenuFactory $menuItem
     * @return string
     */
    public function generateMenuName($menuItem)
    {
        $name = '';
        if ($menuItem->getItemType() == "category") {
            $name = $this->categoryRepository->get($menuItem->getObjectId(), $this->getStoreId())->getName();
        } /* elseif ($menuItem->getItemType() == "pages") {
          $name = $this->pageModel->setStoreId($this->getStoreId())->load($menuItem->getObjectId())->getTitle();
          } */ else {
            $name = $menuItem->getItemName();
}
        return $name;
    }

    /**
     * Retrive Active Class
     */
    public function getActiveClass($menuItem)
    {
        if ($menuItem->getItemType() == 'category') {
            if ($menuItem->getObjectId() == $this->getCurentCat()) {
                return ' active';
            }
        } elseif ($menuItem->getItemType() == 'pages') {
            if ($menuItem->getObjectId() == $this->getCurentPage()) {
                return ' active';
            }
        }
        return '';
    }

    /**
     * Retrieve Html for Mega block
     *
     */
    protected function setMegamenu($menuTree, $childrenWrapClass)
    {
        $html = '';
        $parentId = $menuTree->getItemId();
        $dataclass = $menuTree->getItemClass();
        $animationOption = $menuTree->getAnimationOption();
        $class = $this->getActiveClass($menuTree);


        if ($menuTree->getItemType() == 'megamenu') {
            $class .= ' dropdown';
            $megaMenuLink = $menuTree->getItemLink()?$menuTree->getItemLink():'#';
            $html .= '<li class="menu-dropdown-icon ' . $class . ' ' . $dataclass . '"><a href="'.$megaMenuLink.'" class=""><span class="megaitemicons">' . $menuTree->getItemFontIcon() . '</span> ' . $this->generateMenuName($menuTree) . '</a>';
        } else {
            $sub_cat_disaply = $menuTree->getCategoryDisplay();
            $cat_vertical_menu = $menuTree->getCategoryVerticalMenu();
            $cat_vertical_menu_bg = $menuTree->getCategoryVerticalMenuBg();
            $header_enable = 0;
            $header_block = "";
            $left_enable = 0;
            $left_block = "";
            $right_enable = 0;
            $right_block = "";
            $footer_enable = 0;
            $footer_block = "";
            $header_title = "0";
            $left_title = "0";
            $right_title = "0";
            $footer_title = "0";
            if ($menuTree->getCategoryColumns()) {
                $categoryColumns = json_decode($menuTree->getCategoryColumns());
                foreach ($categoryColumns as $categoryColumn) {
                    if ($categoryColumn->type === 'header') {
                        $header_enable = (int) $categoryColumn->enable;
                        $header_block = $categoryColumn->value;
                        $header_title = $categoryColumn->showtitle;
                    }

                    if ($categoryColumn->type === 'left') {
                        $left_enable = (int) $categoryColumn->enable;
                        $left_block = $categoryColumn->value;
                        $left_title = $categoryColumn->showtitle;
                    }

                    if ($categoryColumn->type === 'right') {
                        $right_enable = (int) $categoryColumn->enable;
                        $right_block = $categoryColumn->value;
                        $right_title = $categoryColumn->showtitle;
                    }

                    if ($categoryColumn->type === 'bottom') {
                        $footer_enable = (int) $categoryColumn->enable;
                        $footer_block = $categoryColumn->value;
                        $footer_title = $categoryColumn->showtitle;
                    }
                }
            }
            $columnCount = 0;
            if ($left_enable) {
                $columnCount = $columnCount + 1;
            }
            if ($right_enable) {
                $columnCount = $columnCount + 1;
            }
            $catDisplay = false;
            $menuAdd = false;
            $verticalMenu = false;
            $verticalMenuClass = '';
            $rightContentClass = '';
            $subcats = [];
            if ($menuTree->getItemType() === 'category' && (int) $sub_cat_disaply === (int) 1) {
                $categoryLoad = $this->categoryRepository->get($menuTree->getObjectId());
                $subcats = $categoryLoad->getChildrenCategories();
                if (count($subcats) > 0) {
                    $catDisplay = true;
                    $menuAdd = true;
                    if ((int) $cat_vertical_menu === (int) 1) {
                        $verticalMenu = true;
                        $verticalMenuClass = 'menu-vertical-wrapper';
                        $rightContentClass = 'col-menu-3';
                    }
                }
            }

            if ($header_enable || $left_enable || $right_enable || $footer_enable) {
                $catDisplay = true;
            }

            $linkurl = $this->generateMenuUrl($menuTree);

            if ($catDisplay) {
                $class .= ' dropdown';
                if ($verticalMenu) {
                    $columnCount = 1;
                } else {
                    $columnCount = $columnCount + 1;
                }

                $menuColumnCount = 1;
                if ($columnCount === 3) {
                    $menuColumnCount = $columnCount - 1;
                }
                if ($columnCount === 2) {
                    $menuColumnCount = $columnCount + 1;
                }
                if ($columnCount === 1) {
                    $menuColumnCount = 4;
                }
                $html .= '<li class="menu-dropdown-icon ' . $class . ' ' . $dataclass . '"><a href="' . $linkurl . '"><span class="megaitemicons">' . $menuTree->getItemFontIcon() . '</span> ' . $this->generateMenuName($menuTree) . '</a>';

                $html .= '<ul class="animated ' . $animationOption . ' column' . $columnCount . " " . $verticalMenuClass . '" style="animation-duration: 0.7s;">';


                if ($header_enable) {
                    $headerblockObject = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($header_block);
                    $headerblock = $this->_blockFactory->create()->load($header_block);
                    $html .= '<li class="megaStaticBlock menu-header">';
                    if ($header_title === '1') {
                        $html .= '<h2>' . $headerblock->getTitle() . '</h2>';
                    }
                    $html .= '<ul><li>' . $headerblockObject->toHtml() . '</li>';
                    $html .= '</ul></li>';
                }

                if ($left_enable && !$verticalMenu) {
                    $leftblockObject = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($left_block);
                    $leftblock = $this->_blockFactory->create()->load($left_block);
                    $html .= '<li class="megaStaticBlock menu-sidebar-left">';
                    if ($left_title === '1') {
                        $html .= '<h2>' . $leftblock->getTitle() . '</h2>';
                    }
                    $html .= '<ul><li>' . $leftblockObject->toHtml() . '</li>';
                    $html .= '</ul></li>';
                }
                if ($right_enable) {
                    $colClass = 'col-menu-9';
                } else {
                    $colClass = '';
                }
                $html .= '<li class="megaStaticBlock menu-content ' . $colClass . '">';

                if (count($subcats) > 0) {
                    if ($verticalMenu) {
                        $verticalHtml = '<div class="col-menu-9 vertical-menu-content">';
                        $html .= '<div class="col-menu-3 vertical-menu-left" style="background:#' . $cat_vertical_menu_bg . ';">';
                        $html .= '<ul class="">';
                        $firstVerticalMenu = 1;
                        $firstVerticalMenuClass = '';
                        $verticalclass = '';
                        foreach ($subcats as $subcat) {
                            if ($subcat->getId() == $this->getCurentCat()) {
                                $verticalclass = 'active';
                            }
                            $_category = $this->categoryRepository->get($subcat->getId());
                            $childrenCats = $_category->getChildrenCategories();
                            /* if ($firstVerticalMenu == 1) {
                              $firstVerticalMenuClass = 'active';
                              } else {
                              $firstVerticalMenuClass = '';
                              } */

                            if (count($childrenCats) > 0) {
                                $addDropdownClass = " dropdown";
                            } else {
                                $addDropdownClass = "";
                            }
                            $html .= '<li class="menu-vertical-items ' . $verticalclass . $addDropdownClass . '" data-toggle="subcat-tab-' . $_category->getId() . '"><a href="' . $_category->getUrl() . '">' . $_category->getName() . '</a></li>';
                            $verticalHtml .= '';
                            if (count($childrenCats) > 0) {
                                if (count($childrenCats) >= 3) {
                                    $columnCountForVerticalMenu = 3;
                                } else {
                                    $columnCountForVerticalMenu = count($childrenCats);
                                }

                                $verticalHtml .= '<div id="subcat-tab-' . $_category->getId() . '" class="vertical-subcate-content ' . $firstVerticalMenuClass . '"><ul class="menu-vertical-child column' . $columnCountForVerticalMenu . '">';
                                foreach ($childrenCats as $childrenCat) {
                                    $verticalclass = '';
                                    if ($childrenCat->getId() == $this->getCurentCat()) {
                                        $verticalclass = 'active';
                                    }
                                    $childrenCatLoad = $this->categoryRepository->get($childrenCat->getId());
                                    $verticalHtml .= '<li class="' . $verticalclass . '"><h4 class="level-3-cat"><a href="' . $childrenCatLoad->getUrl() . '">' . $childrenCatLoad->getName() . '</a></h4>';
                                    $childrenCatsNew = $childrenCatLoad->getChildrenCategories();
                                    if (count($childrenCatsNew) > 0) {
                                        $verticalHtml .= '<ul>';
                                        foreach ($childrenCatsNew as $childrenCatNew) {
                                            $verticalclass = '';
                                            if ($childrenCatNew->getId() == $this->getCurentCat()) {
                                                $verticalclass = 'active';
                                            }
                                            $childrenCatNewLoad = $this->categoryRepository->get($childrenCatNew->getId());
                                            $verticalHtml .= '<li class="' . $verticalclass . '"><a href="' . $childrenCatNewLoad->getUrl() . '">' . $childrenCatNewLoad->getName() . '</a>';
                                            $verticalHtml .= '</li>';
                                        }
                                        $verticalHtml .= '</ul>';
                                    }
                                    $verticalHtml .= '</li>';
                                }
                                $verticalHtml .= '</ul></div>';
                            }
                            $firstVerticalMenu++;
                            //$html .= '</li>';
                        }

                        $verticalHtml .= '</div>';
                        $html .= '</ul>';
                        $html .= '</div>' . $verticalHtml;
                    } else {
                        $html .= '<ul class="column' . $menuColumnCount . '">';
                        foreach ($subcats as $subcat) {
                            $verticalclass = '';
                            if ($subcat->getId() == $this->getCurentCat()) {
                                $verticalclass = 'active';
                            }
                            $_category = $this->categoryRepository->get($subcat->getId());
                            $childrenCats = $_category->getChildrenCategories();

                            $html .= '<li class="' . $verticalclass . '"><h4><a href="' . $_category->getUrl() . '">' . $_category->getName() . '</a></h4>';
                            if (count($childrenCats) > 0) {
                                $html .= '<ul class="level3">';
                                foreach ($childrenCats as $childrenCat) {
                                    $verticalclass = '';
                                    if ($childrenCat->getId() == $this->getCurentCat()) {
                                        $verticalclass = 'active';
                                    }
                                    $childrenCatLoad = $this->categoryRepository->get($childrenCat->getId());
                                    $html .= '<li class="' . $verticalclass . '"><a href="' . $childrenCatLoad->getUrl() . '">' . $childrenCatLoad->getName() . '</a></li>';
                                }
                                $html .= '</ul>';
                            }
                            $html .= '</li>';
                        }
                        $html .= '</ul>';
                    }
                }

                $html .= '</li>';

                if ($right_enable) {
                    $rightblockObject = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($right_block);
                    $rightblock = $this->_blockFactory->create()->load($right_block);
                    $html .= '<li class="megaStaticBlock menu-sidebar-right ' . $rightContentClass . '">';
                    if ($right_title === '1') {
                        $html .= '<h2>' . $rightblock->getTitle() . '</h2>';
                    }
                    $html .= '<ul><li>' . $rightblockObject->toHtml() . '</li>';
                    $html .= '</ul></li>';
                }

                if ($footer_enable) {
                    $footerblockObject = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($footer_block);
                    $footerblock = $this->_blockFactory->create()->load($footer_block);
                    $html .= '<li class="megaStaticBlock menu-footer">';
                    if ($footer_title === '1') {
                        $html .= '<h2>' . $footerblock->getTitle() . '</h2>';
                    }
                    $html .= '<ul><li>' . $footerblockObject->toHtml() . '</li>';
                    $html .= '</ul></li>';
                }

                $html .= '</ul></li>';
            } else {
                $html .= '<li class="' . $class . ' ' . $dataclass . '"><a href="' . $linkurl . '"><span class="megaitemicons">' . $menuTree->getItemFontIcon() . '</span> ' . $this->generateMenuName($menuTree) . '</a></li>';
            }
        }
        $hasChildrenMenu = $menuTree->getItemColumns();
        $menuitemtype = $menuTree->getItemType();
        if (!empty($hasChildrenMenu)) {
            if ($menuitemtype == 'megamenu') {
                $html .= $this->setChildMegamenuColumn($hasChildrenMenu, $animationOption);
                $html .= '</ul></li>';
            }
        }
        return $html;
    }

    /**
     * Retrieve Html for Mega block
     *
     */
    public function setChildMegamenuColumn($childrenMenu, $animationOption)
    {

        $menuitems = json_decode($childrenMenu);
        $totalColumn = count($menuitems);

        $childHtml = '<ul class="animated ' . $animationOption . ' column' . $totalColumn . '">';

        for ($i = 0; $i < $totalColumn; $i++) {
            $type = $menuitems[$i]->type;
            if ($type == 'menu') {
                $subMenuId = $menuitems[$i]->value;

                $menus = $this->_menuFactory->create()->load($subMenuId)->getData();
                $showtitle = $menuitems[$i]->showtitle;
                $childHtml .= '<li class="megaNormalMenu">';
                if ($showtitle == '1') {
                    if (isset($menus['menu_name']) and ! empty($menus['menu_name'])) {
                        $childHtml .= '<h2>' . $menus['menu_name'] . '</h2>';
                    }
                }
                $childHtml .= '<ul>';
                $menuItems = $this->_menuItemsFactory->create()->getCollection()
                        ->addFieldToFilter('menu_id', $subMenuId)
                        ->addFieldToFilter('item_parent_id', 0)
                        ->setOrder('sort_order', 'ASC');

                foreach ($menuItems as $menuitem) {
                    $class = $this->getActiveClass($menuitem);
                    $linkurl = $this->generateMenuUrl($menuitem);
                    $dataclass = $menuitem->getItemClass();

                    $childHtml .= '<li class="' . $class . ' ' . $dataclass . '"><a href="' . $linkurl . '"><span class="megaitemicons">' . $menuitem->getItemFontIcon() . '</span> ' . $this->generateMenuName($menuitem) . '</a></li>';
                }
                $childHtml .= '</ul></li>';
            }
            if ($type == 'block') {
                $subBlockId = $menuitems[$i]->value;
                $blockObject = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($subBlockId);
                $block = $this->_blockFactory->create()->load($subBlockId);
                $childHtml .= '<li class="megaStaticBlock">';
                $showtitle = $menuitems[$i]->showtitle;
                if ($showtitle == '1') {
                    $childHtml .= '<h2>' . $block->getTitle() . '</h2>';
                }
                $childHtml .= '<ul><li>' . $blockObject->toHtml() . '</li></ul></li>';
            }
            if ($type == 'category') {
                $category_id = $menuitems[$i]->value;
//                $subBlockId = $menuitems[$i]->value;
                $category = $this->categoryRepository->get($category_id);
                $subcats = $category->getChildrenCategories();
                $childHtml .= '<li class="megaStaticBlock">';
                $showtitle = $menuitems[$i]->showtitle;
                if (count($subcats) > 0) {
                    if ($showtitle == '1') {
                        $childHtml .= '<h2>' . $category->getName() . '</h2>';
                    }

                    $childHtml .= '<ul>';
                    foreach ($subcats as $subcat) {
                        $_category = $this->categoryRepository->get($subcat->getId());
                        $childHtml .= '<li><a href="' . $_category->getUrl() . '">' . $_category->getName() . '</a></li>';
                    }
                    $childHtml .= '</ul>';
                } else {
                    $childHtml .= '<ul><li><a href="' . $category->getUrl() . '">' . $category->getName() . '</a></li></ul>';
                }

                $childHtml .= '</li>';
            }
        }
        return $childHtml;
    }

    public function getMenuDesign()
    {
        return $this->_primaryMenu->getMenuDesignType();
    }

    public function isSticky()
    {
        return $this->_primaryMenu->getIsSticky();
    }

    public function animationTime()
    {
        return $this->_scopeInterface->getValue(
            'magedelight/general/animation_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
