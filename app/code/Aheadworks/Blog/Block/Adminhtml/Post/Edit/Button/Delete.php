<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button;

use Aheadworks\Blog\Api\PostRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Delete
 * @package Aheadworks\Blog\Block\Adminhtml\Post\Edit\Button
 */
class Delete implements ButtonProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        PostRepositoryInterface $postRepository
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->postRepository = $postRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $postId = $this->request->getParam('id');
        if ($postId && $this->postRepository->get($postId)) {
            $confirmMessage = __('Are you sure you want to do this?');
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => sprintf(
                    "deleteConfirm('%s', '%s')",
                    $confirmMessage,
                    $this->urlBuilder->getUrl('*/*/delete', ['id' => $postId])
                ),
                'sort_order' => 20
            ];
        }
        return $data;
    }
}
