<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Adminhtml\Category;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Model\ResourceModel\Category\Collection;
use Aheadworks\Blog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Button;

/**
 * Class Tree
 * @package Aheadworks\Blog\Block\Adminhtml\Category
 */
class Tree extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Blog::category/tree.phtml';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->addTreeButtons();
        return parent::_prepareLayout();
    }

    /**
     * Add tree buttons
     */
    private function addTreeButtons()
    {
        $currentId = $this->getRequest()->getParam('id', 0);
        $parentId = $this->getRequest()->getParam('parent', 0);
        $parentId = $parentId ? $parentId : $currentId;

        $this->addChild(
            'add_root_button',
            Button::class,
            [
                'label' => __('Add Root Category'),
                'class' => 'add',
                'onclick' => sprintf('window.location.href = "%s"', $this->getUrl('*/*/new')),
                'id' => 'add_category_button'
            ]
        );
        $this->addChild(
            'add_sub_button',
            Button::class,
            [
                'label' => __('Add Subcategory'),
                'class' => 'add',
                'onclick' => sprintf(
                    'window.location.href = "%s"',
                    $this->getUrl('*/*/new', ['parent' => $parentId])
                ),
                'id' => 'add_category_button'
            ]
        );
    }

    /**
     * Retrieve root category button
     *
     * @return string
     */
    public function getAddRootButtonHtml()
    {
        return $this->getChildHtml('add_root_button');
    }

    /**
     * Retrieve sub category button
     *
     * @return string
     */
    public function getAddSubButtonHtml()
    {
        return $this->getChildHtml('add_sub_button');
    }

    /**
     * Retrieve categories data for tree
     *
     * @return array
     */
    private function getCategories()
    {
        $categories = [];
        $currentCategoryId = $this->getRequest()->getParam('id', 0);
        $parentCategoryId = $this->getRequest()->getParam('parent', 0);
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addOrder(CategoryInterface::SORT_ORDER, Collection::SORT_ORDER_ASC);

        /** @var CategoryInterface $category */
        foreach ($collection->getItems() as $category) {
            $categories[] = [
                'id' => $category->getId(),
                'parent' => $category->getParentId() ? $category->getParentId() : '#',
                'text' => $category->getName(),
                'data' => [
                    'sort_order' => $category->getSortOrder()
                ],
                'state' => [
                    'selected' => ($category->getId() == $currentCategoryId || $category->getId() == $parentCategoryId),
                    'opened' => !$category->getParentId()
                ],
                'a_attr' => [
                    'href' => $this->getUrl('*/*/edit', ['id' => $category->getId()])
                ]
            ];
        }

        return $categories;
    }

    /**
     * Retrieve config
     *
     * @return string
     */
    public function getConfig()
    {
        return json_encode([
            'categories' => $this->getCategories(),
            'moveUrl' => $this->getUrl('*/*/move')
        ]);
    }
}
