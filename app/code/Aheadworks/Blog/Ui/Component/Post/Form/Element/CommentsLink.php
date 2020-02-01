<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Ui\Component\Post\Form\Element;

use Magento\Backend\Model\Auth\Session as AuthSession;
use Aheadworks\Blog\Api\CommentsServiceInterface;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class CommentsLink
 * @package Aheadworks\Blog\Ui\Component\Post\Form\Element
 */
class CommentsLink extends Input
{
    /**
     * @var CommentsServiceInterface
     */
    private $commentsService;

    /**
     * @var AuthSession
     */
    private $authSession;

    /**
     * @param ContextInterface $context
     * @param CommentsServiceInterface $commentsService
     * @param AuthSession $authSession
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        CommentsServiceInterface $commentsService,
        AuthSession $authSession,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->commentsService = $commentsService;
        $this->authSession = $authSession;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if (!isset($config['url'])
            && $this->authSession->isAllowed('Aheadworks_Blog::comments')
        ) {
            $config['url'] = $this->commentsService->getModerateUrl();
            $config['linkLabel'] = __('Go To Comments');
            $this->setData('config', $config);
        }
        parent::prepare();
    }
}
