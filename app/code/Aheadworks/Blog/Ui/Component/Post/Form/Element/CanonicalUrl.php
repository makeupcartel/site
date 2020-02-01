<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Ui\Component\Post\Form\Element;

use Magento\Ui\Component\Form\Field;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Convert\DataObject as DataObjectConverter;
use Aheadworks\Blog\Model\Url\TypeResolver as UrlTypeResolver;

/**
 * Class CanonicalUrl
 * @package Aheadworks\Blog\Ui\Component\Post\Form\Element
 */
class CanonicalUrl extends Field
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $options;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var DataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @var UrlTypeResolver
     */
    private $urlTypeResolver;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CategoryRepositoryInterface $categoryRepository
     * @param PostRepositoryInterface $postRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param DataObjectConverter $dataObjectConverter
     * @param UrlTypeResolver $urlTypeResolver
     * @param Config $config
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CategoryRepositoryInterface $categoryRepository,
        PostRepositoryInterface $postRepository,
        SortOrderBuilder $sortOrderBuilder,
        DataObjectConverter $dataObjectConverter,
        UrlTypeResolver $urlTypeResolver,
        Config $config,
        array $components = [],
        array $data = []
    ) {
        $this->config = $config;
        $this->postRepository = $postRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->categoryRepository = $categoryRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->dataObjectConverter = $dataObjectConverter;
        $this->urlTypeResolver = $urlTypeResolver;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');
        if ($this->urlTypeResolver->isCategoryExcl()) {
            $config['componentDisabled'] = true;
        }
        if (!isset($config['options'])) {
            $config['options'] = [];
        }
        $config['options'] = $this->getOptionArray();
        $this->setData('config', $config);
    }

    /**
     * Prepare array with options
     *
     * @return array
     */
    private function getOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->dataObjectConverter->toOptionArray(
                $this->getPostCategories(),
                CategoryInterface::ID,
                CategoryInterface::NAME
            );
        }
        return $this->options;
    }

    /**
     * Get array of post categories
     *
     * @return CategoryInterface[]|array
     */
    private function getPostCategories()
    {
        $categories = [];
        $postId = $this->getContext()->getRequestParam('id');
        if ($postId) {
            try {
                $post = $this->postRepository->get($postId);
                $categoryIds = $post->getCategoryIds();

                $sortOrder = $this->sortOrderBuilder
                    ->setField(CategoryInterface::SORT_ORDER)
                    ->setDirection(SortOrder::SORT_ASC)
                    ->create();
                $this->searchCriteriaBuilder
                    ->addFilter('id', $categoryIds, 'in')
                    ->addSortOrder($sortOrder);
                $categories = $this->categoryRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            } catch (NoSuchEntityException $exception) {
                return $categories;
            }
        }
        return $categories;
    }
}
