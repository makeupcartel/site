<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Post;

use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Model\Post\MetadataProvider;
use Aheadworks\Blog\Api\CategoryRepositoryInterface;

/**
 * Post meta data block
 *
 * @method bool hasPost()
 * @method bool hasCategory()
 * @method MetaData setPost(PostInterface $post)
 * @method MetaData setCategory(CategoryInterface $category)
 * @package Aheadworks\Blog\Block\Post
 */
class MetaData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var MetadataProvider
     */
    private $metadataProvider;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param Context $context
     * @param PostRepositoryInterface $postRepository
     * @param MetadataProvider $metadataProvider
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostRepositoryInterface $postRepository,
        MetadataProvider $metadataProvider,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        $this->metadataProvider = $metadataProvider;
        $this->postRepository = $postRepository;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $postId = $this->getRequest()->getParam('post_id');
        if (!$this->hasPost() && $postId) {
            $this->setPost($this->postRepository->get($postId));
        }
        $categoryId = $this->getRequest()->getParam('blog_category_id');
        if (!$this->hasCategory() && $categoryId) {
            $this->setCategory($this->categoryRepository->get($categoryId));
        }
    }

    /**
     * Get array with open graph meta data
     *
     * @return array
     */
    public function getOpenGraphMetaData()
    {
        if ($this->hasCategory()) {
            return $this->metadataProvider->prepareOpenGraphMetaData($this->getPost(), $this->getCategory());
        }
        return $this->metadataProvider->prepareOpenGraphMetaData($this->getPost());
    }

    /**
     * Get array with twitter meta data
     *
     * @return array
     */
    public function getTwitterMetaData()
    {
        return $this->metadataProvider->prepareTwitterMetaData($this->getPost());
    }
}
