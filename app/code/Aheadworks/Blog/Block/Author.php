<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Api\AuthorRepositoryInterface;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Blog\Model\Image\Info as ImageInfo;
use Magento\Framework\View\Element\Template;
use Aheadworks\Blog\Model\Author as AuthorModel;

/**
 * Author view/list item block
 *
 * @method bool hasAuthor()
 * @method bool hasMode()
 * @method AuthorInterface getAuthor()
 * @method string getMode()
 *
 * @method Author setAuthor(AuthorInterface $author)
 * @method Author setMode(string $mode)
 *
 * @package Aheadworks\Blog\Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Author extends Template implements IdentityInterface
{
    /**
     * List mode
     */
    const LIST_MODE = 'list';

    /**
     * View mode
     */
    const VIEW_MODE = 'view';

    /**
     * @var array
     */
    private $templates = [
        self::LIST_MODE => 'author/mode/list.phtml',
        self::VIEW_MODE => 'author/mode/view.phtml'
    ];

    /**
     * @var AuthorRepositoryInterface
     */
    private $authorRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var ImageInfo
     */
    private $imageInfo;

    /**
     * @param Context $context
     * @param AuthorRepositoryInterface $authorRepository
     * @param Config $config
     * @param Url $url
     * @param ImageInfo $imageInfo
     * @param array $data
     */
    public function __construct(
        Context $context,
        AuthorRepositoryInterface $authorRepository,
        Config $config,
        Url $url,
        ImageInfo $imageInfo,
        array $data = []
    ) {
        $this->authorRepository = $authorRepository;
        $this->config = $config;
        $this->url = $url;
        $this->imageInfo = $imageInfo;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function _construct()
    {
        parent::_construct();
        $authorId = $this->getRequest()->getParam('author_id', 0);

        if (!$this->hasAuthor()) {
            $author = $this->authorRepository->get($authorId);
            $this->setAuthor($author);
        }
        if (!$this->hasMode()) {
            $this->setMode(self::VIEW_MODE);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        $mode = $this->getMode();

        return isset($this->templates[$mode]) ? $this->templates[$mode] : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getMode() == self::VIEW_MODE) {
            $this->prepareBreadcrumbs();
        }
        return $this;
    }

    /**
     * Prepare breadcrumbs
     *
     * @throws LocalizedException
     */
    private function prepareBreadcrumbs()
    {
        /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbs */
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $blogTitle = $this->config->getBlogTitle();

            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbs->addCrumb(
                'blog_home',
                [
                    'label' => $blogTitle,
                    'link' => $this->url->getBlogHomeUrl(),
                ]
            );
            $breadcrumbs->addCrumb(
                'authors',
                [
                    'label' => __('Authors'),
                    'link' => $this->url->getAuthorsPageUrl(),
                ]
            );
            $breadcrumbs->addCrumb(
                'author',
                [
                    'label' => $this->getFullname($this->getAuthor())
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $identities = [AuthorModel::CACHE_TAG . '_' . $this->getAuthor()->getId()];

        return $identities;
    }

    /**
     * Check if image is loaded
     *
     * @return bool
     */
    public function isImageLoaded()
    {
        return $this->getAuthor()->getImageFile() ? true : false;
    }

    /**
     * Get image url
     *
     * @return string
     */
    public function getImageUrl()
    {
        try {
            $url = $this->imageInfo->getMediaUrl($this->getAuthor()->getImageFile());
        } catch (NoSuchEntityException $e) {
            $url = '';
        }

        return $url;
    }

    /**
     * Retrieve author full name
     *
     * @param AuthorInterface $author
     * @return string
     */
    public function getFullname(AuthorInterface $author)
    {
        return $author->getFirstname() . ' ' . $author->getLastname();
    }

    /**
     * Retrieve author image alt
     *
     * @param AuthorInterface $author
     * @return string
     */
    public function getAuthorImageAlt(AuthorInterface $author)
    {
        return __('A photo of %1', $this->getFullname($author));
    }

    /**
     * Retrieve author ulr
     *
     * @param AuthorInterface $author
     * @return string
     */
    public function getAuthorUrl(AuthorInterface $author)
    {
        return $this->url->getAuthorUrl($author);
    }
}
