<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Api\TagRepositoryInterface;
use Aheadworks\Blog\Block\Html\Pager;
use Aheadworks\Blog\Block\Post as PostBlock;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * List of posts block
 * @package Aheadworks\Blog\Block
 */
class PostList extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    /**
     * @var Post\Listing
     */
    protected $postListing;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var TagRepositoryInterface
     */
    protected $tagRepository;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Context $context
     * @param Post\ListingFactory $postListingFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param TagRepositoryInterface $tagRepository
     * @param Url $url
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        Post\ListingFactory $postListingFactory,
        CategoryRepositoryInterface $categoryRepository,
        TagRepositoryInterface $tagRepository,
        Url $url,
        Config $config,
        array $data = []
    ) {
        $this->postListing = $postListingFactory->create();
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->url = $url;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return PostInterface[]
     */
    public function getPosts()
    {
        return $this->postListing->getPosts();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->isNeedPagination()) {
            /** @var Pager $pager */
            $pager = $this->getChildBlock('pager');
            if ($pager) {
                $pager
                    ->setPath(trim($this->getRequest()->getPathInfo(), '/'))
                    ->setLimit($this->config->getNumPostsPerPage());
                $this->postListing->applyPagination($pager);
            }
        }
        /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbs */
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );

            $tagId = (int)$this->getRequest()->getParam('tag_id');
            $categoryId = (int)$this->getRequest()->getParam('blog_category_id');

            $blogTitle = $this->config->getBlogTitle();
            if (!$tagId && !$categoryId) {
                $breadcrumbs->addCrumb('blog_home', ['label' => $blogTitle]);
            } else {
                $breadcrumbs->addCrumb(
                    'blog_home',
                    [
                        'label' => $blogTitle,
                        'link' => $this->url->getBlogHomeUrl(),
                    ]
                );
                if ($tagId) {
                    $tag = $this->tagRepository->get($tagId);
                    $breadcrumbs->addCrumb(
                        'search_by_tag',
                        ['label' => __("Tagged with '%1'", $tag->getName())]
                    );
                }
                if ($categoryId) {
                    $category = $this->categoryRepository->get($categoryId);
                    $crumbInfo = ['label' => $category->getName()];
                    if ($this->getRequest()->getParam('post_id')) {
                        $crumbInfo['link'] = $this->url->getCategoryUrl($category);
                    }
                    $breadcrumbs->addCrumb('category_view', $crumbInfo);
                }
            }
        }
        return $this;
    }

    /**
     * Retrieves items list html
     *
     * @param PostInterface $post
     * @return string
     */
    public function getItemHtml(PostInterface $post)
    {
        /** @var PostBlock $block */
        $block = $this->getLayout()->createBlock(
            PostBlock::class,
            '',
            [
                'data' => [
                    'post' => $post,
                    'mode' => PostBlock::MODE_LIST_ITEM,
                    // Temporary solution.
                    // Will be revised in the scope of https://magento2.atlassian.net/browse/BB-189
                    'social_icons_block' => $this->getSocialIconsBlock()
                ]
            ]
        );
        return $block->toHtml();
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getPosts() as $post) {
            $identities = [\Aheadworks\Blog\Model\Post::CACHE_TAG . '_' . $post->getId()];
        }
        if ($categoryId = (int)$this->getRequest()->getParam('blog_category_id')) {
            $identities = [\Aheadworks\Blog\Model\Category::CACHE_TAG . '_' . $categoryId];
        }
        if ($tagId = (int)$this->getRequest()->getParam('tag_id')) {
            $identities = [\Aheadworks\Blog\Model\Tag::CACHE_TAG . '_'
                . $this->tagRepository->get($tagId)->getName()];
        }
        if (!$categoryId && !$tagId) {
            $identities[] = \Aheadworks\Blog\Model\Post::CACHE_TAG_LISTING;
        }
        return $identities;
    }

    /**
     * Check if there is at least one post, in this case pagination can be used
     *
     * @return int
     */
    private function isNeedPagination()
    {
        $this->postListing->getSearchCriteriaBuilder()->setPageSize(1);
        return count($this->getPosts());
    }
}
