<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Observer;

use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Model\Category;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Source\Category\Status as CategoryStatus;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Registry;

/**
 * Class AddBlogToTopmenuItemsObserver
 * @package Aheadworks\Blog\Observer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddBlogToTopmenuItemsObserver implements ObserverInterface
{
    /**
     * @var string
     */
    const NODE_ID_PREFIX = 'blog-menu-item-node';

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var array
     */
    private $addedNodes = [];

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Url $url
     * @param Config $config
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param NodeFactory $nodeFactory
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Url $url,
        Config $config,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        NodeFactory $nodeFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->url = $url;
        $this->config = $config;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->nodeFactory = $nodeFactory;
    }

    /**
     * Retrieve categories
     *
     * @return CategoryInterface[]
     */
    private function getCategories()
    {
        $this->searchCriteriaBuilder
            ->addFilter(CategoryInterface::STATUS, CategoryStatus::ENABLED)
            ->addFilter(CategoryInterface::STORE_IDS, $this->storeManager->getStore()->getId())
            ->addSortOrder(
                new SortOrder(
                    [
                        SortOrder::FIELD => CategoryInterface::SORT_ORDER,
                        SortOrder::DIRECTION => SortOrder::SORT_ASC
                    ]
                )
            );
        return $this->categoryRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->isNeedToAdd()) {
            /** @var \Magento\Theme\Block\Html\Topmenu $menuBlock */
            $menuBlock = $observer->getEvent()->getBlock();
            $menuBlock->addIdentity(Category::CACHE_TAG);

            $blogHomeItem = $this->addItem($this->getMenuItemData(), $menuBlock->getMenu());
            foreach ($this->getCategories() as $category) {
                if ($parentNode = $this->resolveParentNode($category, $blogHomeItem)) {
                    $this->addedNodes[$category->getId()] = $this->addItem(
                        $this->getMenuItemData($category),
                        $parentNode
                    );
                    $menuBlock->addIdentity(Category::CACHE_TAG . '_' . $category->getId());
                }
            }
        }
    }

    /**
     * Add item
     *
     * @param array $itemData
     * @param Node $parentNode
     * @return Node
     */
    private function addItem($itemData, $parentNode)
    {
        $menuNode = $this->nodeFactory->create(
            [
                'data' => $itemData,
                'idField' => 'id',
                'tree' => $parentNode->getTree(),
                'parent' => $parentNode
            ]
        );
        $parentNode->addChild($menuNode);
        return $menuNode;
    }

    /**
     * Retrieve data for menu item
     *
     * @param CategoryInterface|null $category
     * @return array
     */
    private function getMenuItemData(CategoryInterface $category = null)
    {
        if ($category instanceof CategoryInterface) {
            $nodeId = self::NODE_ID_PREFIX . '-' . $category->getId();
            $name = $category->getName();
            $url = $this->url->getCategoryUrl($category);
            $hasActive = false;
            $isActive = $this->isCategoryActive($category);
        } else {
            $nodeId = self::NODE_ID_PREFIX;
            $name = $this->config->getBlogTitle();
            $url = $this->url->getBlogHomeUrl();
            $hasActive = $this->isBlogCategoryActive();
            $isActive = $this->isBlogHomeActive();
        }

        return [
            'id' => $nodeId,
            'name' => $name,
            'url' => $url,
            'has_active' => $hasActive,
            'is_active' => $isActive
        ];
    }

    /**
     * Checks whether the blog home item is active
     *
     * @return bool
     */
    private function isBlogHomeActive()
    {
        return (bool)$this->coreRegistry->registry('blog_action');
    }

    /**
     * Checks if any of blog categories is active
     *
     * @return bool
     */
    private function isBlogCategoryActive()
    {
        foreach ($this->getCategories() as $category) {
            if ($this->isCategoryActive($category)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks whether the given category is active
     *
     * @param CategoryInterface $category
     * @return bool
     */
    private function isCategoryActive(CategoryInterface $category)
    {
        return $this->request->getParam('blog_category_id') == $category->getId();
    }

    /**
     * @param CategoryInterface $category
     * @param Node $blogHomeNode
     * @return bool|Node
     */
    private function resolveParentNode($category, $blogHomeNode)
    {
        if ($category->getParentId()) {
            $parentNode = isset($this->addedNodes[$category->getParentId()])
                ? $this->addedNodes[$category->getParentId()]
                : false;
        } else {
            $parentNode = $blogHomeNode;
        }

        return $parentNode;
    }

    /**
     * Check is need to add to nav menu
     *
     * @return bool
     */
    private function isNeedToAdd()
    {
        return $this->config->isBlogEnabled() && $this->config->isMenuLinkEnabled();
    }
}
