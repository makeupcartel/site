<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Post;

use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Model\Url;
use Aheadworks\Blog\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Aheadworks\Blog\Api\Data\CategoryInterface;

/**
 * Class MetadataProvider
 * @package Aheadworks\Blog\Model\Post
 */
class MetadataProvider
{
    /**#@+
     * Constants defined for keys of the meta-data.
     */
    const OG_SITE_NAME = 'og:site_name';
    const OG_LOCALE = 'og:locale';
    const OG_TYPE = 'og:type';
    const TYPE = 'article';
    const OG_IMAGE = 'og:image';
    const OG_TITLE = 'og:title';
    const OG_DESCRIPTION = "og:description";
    const OG_URL = 'og:url';
    const FB_APP_ID = 'fb:app_id';
    const TWITTER_CARD_TYPE = 'twitter:card';
    const CARD_TYPE = 'summary_large_image';
    const TWITTER_SITE = 'twitter:site';
    const TWITTER_CREATOR = 'twitter:creator';

    /**
     * @var Url
     */
    private $url;

    /**
     * @var FeaturedImageInfo
     */
    private $imageInfo;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Url $url
     * @param FeaturedImageInfo $imageInfo
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param LocaleResolver $localeResolver
     */
    public function __construct(
        Url $url,
        FeaturedImageInfo $imageInfo,
        Config $config,
        StoreManagerInterface $storeManager,
        LocaleResolver $localeResolver
    ) {
        $this->url = $url;
        $this->imageInfo = $imageInfo;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Prepare array with open graph meta data
     *
     * @param PostInterface $post
     * @param CategoryInterface|null $category
     * @return array
     */
    public function prepareOpenGraphMetaData(PostInterface $post, CategoryInterface $category = null)
    {
        $openGraphData = [];
        if ($ogUrl = $this->getMetaOgUrl($post, $category)) {
            $openGraphData[self::OG_URL]  = $ogUrl;
        }
        $openGraphData[self::OG_TYPE]  = self::TYPE;
        if ($ogTitle = $post->getTitle()) {
            $openGraphData[self::OG_TITLE]  = $ogTitle;
        }
        if ($ogDescription = $this->getMetaOgDescription($post)) {
            $openGraphData[self::OG_DESCRIPTION]  = $ogDescription;
        }
        if ($ogImage = $this->getMetaOgImage($post)) {
            $openGraphData[self::OG_IMAGE] = $ogImage;
        }
        if ($ogSiteName = $this->getMetaOgSiteName()) {
            $openGraphData[self::OG_SITE_NAME] = $ogSiteName;
        }
        if ($ogLocale = $this->getMetaOgLocale()) {
            $openGraphData[self::OG_LOCALE] = $ogLocale;
        }
        if ($fbAppId = $this->getMetaFacebookAppId()) {
            $openGraphData[self::FB_APP_ID] = $fbAppId;
        }

        return $openGraphData;
    }

    /**
     * Prepare array with twitter meta data
     *
     * @param PostInterface $post
     * @return array
     */
    public function prepareTwitterMetaData(PostInterface $post)
    {
        $twitterMetaData = [];
        $twitterMetaData[self::TWITTER_CARD_TYPE]  = self::CARD_TYPE;
        if ($twitterSite = $this->getMetaTwitterSite($post)) {
            $twitterMetaData[self::TWITTER_SITE]  = $twitterSite;
        }
        if ($twitterCreator = $this->getMetaTwitterCreator($post)) {
            $twitterMetaData[self::TWITTER_CREATOR]  = $twitterCreator;
        }

        return $twitterMetaData;
    }

    /**
     * Retrieve og:description meta tag from post
     *
     * @param PostInterface $post
     * @return string
     */
    private function getMetaOgDescription($post)
    {
        $content = $this->getClearContent($post->getShortContent());
        if (strlen($content) == 0) {
            $content = $this->getClearContent($post->getContent());
        }
        if (!$content) {
            $content = $post->getMetaDescription();
        }
        return $content;
    }

    /**
     * Retrieve clear content
     *
     * @param string $content
     * @return string
     */
    private function getClearContent($content)
    {
        $lenContent = 256;
        $content = trim(strip_tags($content));

        return strlen($content) > $lenContent
            ? substr($content, 0, $lenContent)
            : $content;
    }

    /**
     * Get meta open graph url
     *
     * @param $post
     * @param $category
     * @return string
     */
    private function getMetaOgUrl($post, $category)
    {
        return $this->url->getPostUrl($post, $category);
    }

    /**
     * Get meta open graph image
     *
     * @param PostInterface $post
     * @return string
     */
    private function getMetaOgImage($post)
    {
        return $this->imageInfo->getImageUrl($post->getFeaturedImageFile());
    }

    /**
     * Get meta open graph site name
     *
     * @return null|string
     */
    private function getMetaOgSiteName()
    {
        return $this->config->getStoreName($this->storeManager->getStore()->getId());
    }

    /**
     * Get meta open graph locale
     *
     * @return null|string
     */
    private function getMetaOgLocale()
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * Get meta facebook application ID
     *
     * @return null|string
     */
    private function getMetaFacebookAppId()
    {
        return $this->config->getFacebookAppId($this->storeManager->getStore()->getId());
    }

    /**
     * Get meta twitter site
     *
     * @param PostInterface $post
     * @return null|string
     */
    private function getMetaTwitterSite($post)
    {
        $twitterSite = $post->getMetaTwitterSite()
            ? $post->getMetaTwitterSite()
            : $this->config->getMetaTwitterSite($this->storeManager->getStore()->getId());
        return $twitterSite;
    }

    /**
     * Get meta twitter creator
     *
     * @param PostInterface $post
     * @return null|string
     */
    private function getMetaTwitterCreator($post)
    {
        $author = $post->getAuthor();

        return $author ? $author->getTwitterId() : null;
    }
}
