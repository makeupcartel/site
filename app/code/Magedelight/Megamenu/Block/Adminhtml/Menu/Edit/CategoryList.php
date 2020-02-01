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

namespace Magedelight\Megamenu\Block\Adminhtml\Menu\Edit;

/**
 * Class CategoryList
 *
 * @package Magedelight\Megamenu\Block\Adminhtml\Menu\Edit
 */
class CategoryList extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $_buttonList;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_category;

    /**
     * @var array
     */
    protected $_allStoreIds = [];

    /**
     * @var array
     */
    protected $_selectedStoreIds = [];

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $categoryModel;
    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Backend\Model\Auth\Session $backendSession
     * @param \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Backend\Model\Auth\Session $backendSession,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList,
        \Magento\Catalog\Model\Category $categoryModel,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        array $data = []
    ) {
        $this->_buttonList = $buttonList;
        $this->_category = $categoryFactory;
        $this->categoryModel = $categoryModel;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $jsonEncoder, $resourceHelper, $backendSession, $data);
        $this->mapStoreIds();
    }

    /**
     * Retrieve category by category id
     *
     * @param string $category_id
     * @return Magento\Catalog\Model\Category
     */
    public function get_category($category_id = '')
    {
        return $this->categoryModel->load($category_id);
    }

    /**
     * @param mixed|null $parenNodeCategory
     * @return string
     */
    public function getTreeJson($parenNodeCategory = null)
    {
        $rootArray = $this->_getNodeJson($parenNodeCategory);
        $json = $this->_jsonEncoder->encode(isset($rootArray['children']) ? $rootArray['children'] : []);
        return $json;
    }

    /**
     * Get JSON of a tree node or an associative array
     *
     * @param Node|array $node
     * @param int $level
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Node($node, 'entity_id', new \Magento\Framework\Data\Tree());
        }

        $item = [];
        $item['text'] = $this->buildNodeName($node);

        $rootForStores = in_array($node->getEntityId(), $this->getRootIds());

        $item['id'] = $node->getId();
        $item['store'] = (int) $this->getStore()->getId();
        $item['path'] = $node->getData('path');

        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        //$item['allowDrop'] = ($level<3) ? true : false;
        $allowMove = $this->_isCategoryMoveable($node);
        $item['allowDrop'] = $allowMove;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = $allowMove && ($node->getLevel() == 1 && $rootForStores ? false : true);

        $isParent = $this->_isParentSelectedCategory($node);
        
        if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                if (!is_array($node->getChildren())) {
                    $newarray = array_filter(explode(',', $node->getChildren()));
                }
                $childrenCount = count($newarray);
         }

        if ($childrenCount > 0) {
            $item['children'] = [];
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($newarray as $child) {
                    $childCategory = $this->categoryRepository->get($child);
                    $item['children'][] = $this->_getNodeJson($childCategory, $level + 1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        return $item;
    }

    /**
     * Retrive the root categories for stores selected for a particular menu
     * @return array
     */
    public function get_root_category_from_store()
    {
        $menublock = $this->getParentBlock();
        $menu = $menublock->get_menu_from_menu_id();
        foreach ($menu->getStoreId() as $storeId) {
            $store = $this->_storeManager->getStore($storeId);
            $this->_selectedStoreIds[$storeId] = $store->getRootCategoryId();
        }
        return $this->_selectedStoreIds;
    }

    /**
     * Map all stores with their root category by key value pair
     */
    public function mapStoreIds()
    {
        $stores = $this->_storeManager->getStores();
        foreach ($stores as $store) {
            $this->_allStoreIds[$store->getStoreId()] = $store->getRootCategoryId();
        }
    }

    /**
     * Retrive the root category id base on store selected
     * @return string|bool
     */
    public function find_root_category()
    {
        $rootCategories = $this->get_root_category_from_store();
        if (in_array(0, $rootCategories)) { /* all storeview selected */
            if (count(array_unique($this->_allStoreIds)) === 1) {
                return end($this->_allStoreIds);
            }
        } else {
            if (count(array_unique($this->_selectedStoreIds)) === 1) {
                return end($this->_selectedStoreIds);
            }
        }
        return false;
    }
}
