<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Adminhtml\Author\Edit\Button;

use Aheadworks\Blog\Api\AuthorRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Delete
 * @package Aheadworks\Blog\Block\Adminhtml\Author\Edit\Button
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
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param AuthorRepositoryInterface $authorRepository
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        AuthorRepositoryInterface $authorRepository
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->authorRepository = $authorRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $data = [];
        $authorId = $this->request->getParam('id');
        if ($authorId && $this->authorRepository->get($authorId)) {
            $confirmMessage = __('Are you sure you want to do this?');
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => sprintf(
                    "deleteConfirm('%s', '%s')",
                    $confirmMessage,
                    $this->urlBuilder->getUrl('*/*/delete', ['id' => $authorId])
                ),
                'sort_order' => 20
            ];
        }
        return $data;
    }
}
