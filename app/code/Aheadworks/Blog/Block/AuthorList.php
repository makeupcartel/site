<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Block\Author\Listing;
use Aheadworks\Blog\Block\Html\Pager;
use Aheadworks\Blog\Model\Author;
use Aheadworks\Blog\Model\Config;
use Aheadworks\Blog\Model\Url;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Aheadworks\Blog\Block\Author\ListingFactory;
use Aheadworks\Blog\Block\Author as AuthorBlock;

/**
 * List of authors block
 * @package Aheadworks\Blog\Block
 */
class AuthorList extends Template implements IdentityInterface
{
    /**
     * @var Listing
     */
    private $authorListing;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param ListingFactory $authorListingFactory
     * @param Url $url
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ListingFactory $authorListingFactory,
        Url $url,
        Config $config,
        array $data = []
    ) {
        $this->authorListing = $authorListingFactory->create();
        $this->url = $url;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return AuthorInterface[]|null|false
     */
    public function getAuthors()
    {
        try {
            $authors = $this->authorListing->getAuthors();
        } catch (LocalizedException $e) {
            $authors = false;
        }

        return $authors;
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
                $pager->setPath(trim($this->getRequest()->getPathInfo(), '/'));
                $this->authorListing->applyPagination($pager);
            }
        }
        $this->prepareBreadcrumbs();

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
        }
    }

    /**
     * Author item rendering
     *
     * @param AuthorInterface $author
     * @return string
     */
    public function getItemHtml(AuthorInterface $author)
    {
        try {
            /** @var AuthorBlock $block */
            $block = $this->getLayout()->createBlock(
                AuthorBlock::class,
                '',
                [
                    'data' => [
                        'author' => $author,
                        'mode' => AuthorBlock::LIST_MODE
                    ]
                ]
            );
            $html = $block->toHtml();
        } catch (LocalizedException $e) {
            $html = '';
        }

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $identities = [Author::LISTING_CACHE_TAG];
        foreach ($this->getAuthors() as $author) {
            $identities = [Author::CACHE_TAG . '_' . $author->getId()];
        }
        return $identities;
    }

    /**
     * Check if there is at least one author, in this case pagination can be used
     *
     * @return int
     */
    private function isNeedPagination()
    {
        $this->authorListing->getSearchCriteriaBuilder()->setPageSize(1);
        return count($this->getAuthors());
    }
}
