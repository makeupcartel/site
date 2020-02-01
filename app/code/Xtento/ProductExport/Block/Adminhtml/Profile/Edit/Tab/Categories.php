<?php

/**
 * Product:       Xtento_ProductExport
 * ID:            SxbX7cG9QyeSp8Mf+48x0gACKjo+bC67h/GgYBgB9YA=
 * Last Modified: 2019-08-29T15:43:16+00:00
 * File:          app/code/Xtento/ProductExport/Block/Adminhtml/Profile/Edit/Tab/Categories.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\ProductExport\Block\Adminhtml\Profile\Edit\Tab;

use Magento\Framework\Exception\NoSuchEntityException;
use Xtento\ProductExport\Model\Export;
use Magento\Framework\Data\Tree\Node;

class Categories extends \Xtento\ProductExport\Block\Adminhtml\Widget\Tab implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
     */
    protected $categoryTree;

    /**
     * @var \Xtento\ProductExport\Helper\Module
     */
    protected $moduleHelper;

    protected $builtCategoryTree;
    protected $categoryMapping;

    /**
     * Categories constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Xtento\ProductExport\Helper\Module $moduleHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Xtento\ProductExport\Helper\Module $moduleHelper,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->categoryTree = $categoryTree;
        $this->moduleHelper = $moduleHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function getFormMessages()
    {
        $formMessages = [];
        $formMessages[] = [
            'type' => 'notice',
            'message' => __(
                'Easily map your categories to other taxonomies such as the Google Taxonomy ("google_product_category"). For each of your categories, you can easily specify the "mapped" category you\'d like to output for products instead. Our ready-to-use feeds will automatically use that category then, if you use a custom-made feed, in the tab "Output Format", then use the following code to output the mapped product category: &lt;xsl:value-of select="xtento_mapped_category"/&gt;'
            )
        ];
        return $formMessages;
    }

    /**
     * @return $this|\Xtento\ProductExport\Block\Adminhtml\Widget\Tab
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $this->setForm($form);
        $this->setTemplate('Xtento_ProductExport::profile/categories.phtml');
        return parent::_prepareForm();
    }
    
    public function getProfile() 
    {
        return $this->_coreRegistry->registry('productexport_profile');
    }

    public function getTaxonomies()
    {
        $taxonomies = $this->moduleHelper->getTaxonomies();
        foreach ($taxonomies as $code => $label) {
            $explodedCode = explode("_", $code);
            $taxonomies[$code] = sprintf('[%s] %s', ucfirst(array_shift($explodedCode)), implode("_", $explodedCode));
        }
        return $taxonomies;
    }

    public function getCategoryTree()
    {
        if ($this->builtCategoryTree !== null) {
            return $this->builtCategoryTree;
        }

        $profile = $this->_coreRegistry->registry('productexport_profile');
        $this->categoryMapping = json_decode($profile->getCategoryMapping(), true) ?: [];
        $storeId = $profile->getStoreId();
        if ($storeId) {
            $store = $this->_storeManager->getStore($storeId);
            $rootCategoryId = $store->getRootCategoryId();
        } else {
            $rootCategoryId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        }

        try {
            $category = $this->categoryRepository->get($rootCategoryId, $storeId);
        } catch (NoSuchEntityException $e) {
            return [];
        }
        $this->builtCategoryTree = $this->_getNodeJson($this->getNode($category, 10));
        return $this->builtCategoryTree;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCategoryCollection()
    {
        $profile = $this->_coreRegistry->registry('productexport_profile');
        $storeId = $profile->getStoreId();
        $collection = $this->getData('category_collection');
        if ($collection === null) {
            $collection = $this->categoryCollectionFactory->create();

            $collection->addAttributeToSelect(
                'name'
            )->addAttributeToSelect(
                'is_active'
            )->setProductStoreId(
                $storeId
            )->setStoreId(
                $storeId
            );

            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    /**
     * @param mixed $parentNodeCategory
     * @param int $recursionLevel
     *
     * @return Node
     */
    public function getNode($parentNodeCategory, $recursionLevel = 2)
    {
        $nodeId = $parentNodeCategory->getId();
        $node = $this->categoryTree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);

        if ($node && $nodeId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif ($node && $node->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            $node->setName(__('Root'));
        }

        $this->categoryTree->addCollectionData($this->getCategoryCollection());
        return $node;
    }

    /**
     * @param $node
     * @param int $level
     *
     * @return array
     */
    protected function _getNodeJson($node, $level = 0)
    {
        if (is_array($node)) {
            $node = new Node($node, 'entity_id', new \Magento\Framework\Data\Tree());
        }

        $item = [];
        $item['name'] = $node->getName();
        $item['id'] = $node->getId();
        $item['mappedValue'] = isset($this->categoryMapping[$node->getId()]) ? $this->categoryMapping[$node->getId()] : '';

        if ((int)$node->getChildrenCount() > 0) {
            $item['children'] = [];
        }
        if ($node->hasChildren()) {
            $item['children'] = [];
            foreach ($node->getChildren() as $child) {
                $item['children'][] = $this->_getNodeJson($child, $level + 1);
            }
        }

        return $item;
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Category Mapping');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Category Mapping');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        $model = $this->_coreRegistry->registry('productexport_profile');
        if ($model->getEntity() !== Export::ENTITY_PRODUCT) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}