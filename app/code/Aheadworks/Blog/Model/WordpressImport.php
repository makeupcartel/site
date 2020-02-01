<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model;

use Aheadworks\Blog\Api\CategoryRepositoryInterface;
use Aheadworks\Blog\Api\Data\CategoryInterface;
use Aheadworks\Blog\Api\Data\CategoryInterfaceFactory;
use Aheadworks\Blog\Api\Data\PostInterface;
use Aheadworks\Blog\Api\PostRepositoryInterface;
use Aheadworks\Blog\Model\Post\Author\Resolver as AuthorResolver;
use Aheadworks\Blog\Model\Source\Post\Status as PostStatus;
use Aheadworks\Blog\Model\Source\Category\Status as CategoryStatus;
use Magento\Backend\Model\Auth;
use Magento\Framework\Api\DataObjectHelper;
use Aheadworks\Blog\Api\Data\PostInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Blog\Model\Converter\Condition as ConditionConverter;
use Aheadworks\Blog\Model\Rule\ProductFactory;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class WordpressImport
 * @package Aheadworks\Blog\Model
 */
class WordpressImport
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var PostInterfaceFactory
     */
    private $postDataFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    private $auth;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CategoryInterfaceFactory
     */
    private $categoryDataFactory;

    /**
     * @var ConditionConverter
     */
    private $conditionConverter;

    /**
     * @var ProductFactory
     */
    private $productRuleFactory;

    /**
     * @var string
     */
    private $wpBaseUrl = '';

    /**
     * @var array
     */
    private $wpAuthors = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var AuthorResolver
     */
    private $authorResolver;

    /**
     * @param Config $config
     * @param PostInterfaceFactory $postDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param PostRepositoryInterface $postRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryInterfaceFactory $categoryDataFactory
     * @param Auth $auth
     * @param ConditionConverter $conditionConverter
     * @param ProductFactory $productRuleFactory
     * @param StoreManagerInterface $storeManager
     * @param FilterManager $filterManager
     * @param DateTime $dateTime
     * @param AuthorResolver $authorResolver
     */
    public function __construct(
        Config $config,
        PostInterfaceFactory $postDataFactory,
        DataObjectHelper $dataObjectHelper,
        PostRepositoryInterface $postRepository,
        CategoryRepositoryInterface $categoryRepository,
        CategoryInterfaceFactory $categoryDataFactory,
        Auth $auth,
        ConditionConverter $conditionConverter,
        ProductFactory $productRuleFactory,
        StoreManagerInterface $storeManager,
        FilterManager $filterManager,
        DateTime $dateTime,
        AuthorResolver $authorResolver
    ) {
        $this->config = $config;
        $this->postDataFactory = $postDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->postRepository = $postRepository;
        $this->auth = $auth;
        $this->categoryRepository = $categoryRepository;
        $this->categoryDataFactory = $categoryDataFactory;
        $this->conditionConverter = $conditionConverter;
        $this->productRuleFactory = $productRuleFactory;
        $this->storeManager = $storeManager;
        $this->filterManager = $filterManager;
        $this->dateTime = $dateTime;
        $this->authorResolver = $authorResolver;
    }

    /**
     * Import posts
     *
     * @param string $filePath
     * @param bool $canOverride
     * @return int
     */
    public function import($filePath, $canOverride)
    {
        $xml = simplexml_load_file($filePath);
        $this->wpBaseUrl = $this->getWpBaseUrl($xml);
        $this->wpAuthors = $this->getWpAuthors($xml);

        $importedCount = 0;
        foreach ($this->getPosts($xml) as $post) {
            try {
                $postData = $this->getPreparedPostData($post);
                $postId = isset($postData[PostInterface::ID]) ? $postData[PostInterface::ID] : null;
                if ($postId && !$canOverride) {
                    continue;
                }

                $postDataObject = $this->postDataFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $postDataObject,
                    $postData,
                    PostInterface::class
                );
                $this->postRepository->save($postDataObject);
                $importedCount++;
            } catch (\Magento\Framework\Validator\Exception $exception) {
            } catch (LocalizedException $exception) {
            } catch (\Exception $exception) {
            }
        }
        return $importedCount;
    }

    /**
     * Retrieve WP post base url
     *
     * @param \SimpleXmlElement $xml
     * @return string
     */
    private function getWpBaseUrl($xml)
    {
        $wpLinkArray = $xml->xpath('channel/link');
        return (string)array_shift($wpLinkArray);
    }

    /**
     * Retrieve WP post authors as array items author_login => author_display_name
     *
     * @param \SimpleXmlElement $xml
     * @return array
     */
    private function getWpAuthors($xml)
    {
        $authors = [];
        $namespaces = $xml->getNamespaces(true);
        $wpNamespaceXml = $xml->xpath('channel')[0]->children($namespaces['wp']);

        foreach ($wpNamespaceXml->author as $author) {
            $authors[(string)$author->author_login] = (string)$author->author_display_name;
        }

        return $authors;
    }

    /**
     * Retrieve WP posts as array
     *
     * @param \SimpleXmlElement $xml
     * @return array
     */
    private function getPosts($xml)
    {
        return $xml->xpath('channel/item');
    }

    /**
     * Get prepared post data
     *
     * @param \SimpleXmlElement $post
     * @return array
     */
    private function getPreparedPostData($post)
    {
        $postData = [
            PostInterface::TITLE                => $this->getPostTitle($post),
            PostInterface::URL_KEY              => $this->getPostUrlKey($post),
            'has_short_content'                 => 0,
            PostInterface::SHORT_CONTENT        => '',
            PostInterface::META_DESCRIPTION     => '',
            PostInterface::STATUS               => $this->getPostStatus($post),
            PostInterface::PUBLISH_DATE         => $this->getPostPublishDate($post),
            PostInterface::CONTENT              => $this->getPostContent($post),
            PostInterface::IS_ALLOW_COMMENTS    => true,
            PostInterface::CATEGORY_IDS         => $this->getPostCategoryIds($post),
            PostInterface::TAG_NAMES            => $this->getPostTags($post),
            PostInterface::AUTHOR_ID            => $this->getPostAuthorId($post),
            PostInterface::STORE_IDS            => [Store::DEFAULT_STORE_ID],
            PostInterface::PRODUCT_CONDITION    => $this->getPostDefaultProductConditions()
        ];
        if ($postId = $this->getPostId($post)) {
            $postData[PostInterface::ID] = $postId;
        }

        return $postData;
    }

    /**
     * Retrieve title for post
     *
     * @param \SimpleXmlElement $post
     * @return string
     */
    private function getPostTitle($post)
    {
        return (string)$post->title;
    }

    /**
     * Retrieve url key for post
     *
     * @param \SimpleXmlElement $post
     * @return string
     */
    private function getPostUrlKey($post)
    {
        $namespaces = $post->getNamespaces(true);
        $wpNamespaceItems = $post->children($namespaces['wp']);
        $postUrlKey = (string)$wpNamespaceItems->post_name;
        if (empty($postUrlKey)) {
            $postTitle = $this->getPostTitle($post);
            $postUrlKey = $this->filterManager->translitUrl($postTitle);
        }
        return $postUrlKey;
    }

    /**
     * Retrieve blog post status
     *
     * @param \SimpleXmlElement $post
     * @return string
     */
    private function getPostStatus($post)
    {
        $wpPostStatus = $this->getWpPostStatus($post);
        return $this->getBlogPostStatus($wpPostStatus);
    }

    /**
     * Retrieve WP post status
     *
     * @param \SimpleXmlElement $post
     * @return string
     */
    private function getWpPostStatus($post)
    {
        $namespaces = $post->getNamespaces(true);
        $wpNamespaceItems = $post->children($namespaces['wp']);
        return (string)$wpNamespaceItems->status;
    }

    /**
     * Get blog post status based on WP status
     *
     * @param string $wpPostStatus
     * @return string
     */
    private function getBlogPostStatus($wpPostStatus)
    {
        $blogPostStatus = $this->getDefaultPostStatus();
        $statusesMapping = $this->getPostStatusesMapping();
        if (isset($statusesMapping[$wpPostStatus])) {
            $blogPostStatus = $statusesMapping[$wpPostStatus];
        }
        return $blogPostStatus;
    }

    /**
     * Get mappings between WP and blog post statuses
     *
     * @return array
     */
    private function getPostStatusesMapping()
    {
        return [
            'publish' => PostStatus::PUBLICATION,
            'draft' => PostStatus::DRAFT
        ];
    }

    /**
     * Get default blog post status for import
     *
     * @return string
     */
    private function getDefaultPostStatus()
    {
        return PostStatus::DRAFT;
    }

    /**
     * Retrieve formatted publish date for post
     *
     * @param \SimpleXmlElement $post
     * @return string
     */
    private function getPostPublishDate($post)
    {
        $wpPostPublishDate = $this->getWpPostPublishDate($post);
        return $this->getPreparedPublishDate($wpPostPublishDate);
    }

    /**
     * Retrieve WP post publish date
     *
     * @param \SimpleXmlElement $post
     * @return string
     */
    private function getWpPostPublishDate($post)
    {
        $namespaces = $post->getNamespaces(true);
        $wpNamespaceItems = $post->children($namespaces['wp']);
        return (string)$wpNamespaceItems->post_date_gmt;
    }

    /**
     * Get prepared publish date for post
     *
     * @param string $wpPostPublishDate
     * @return string
     */
    private function getPreparedPublishDate($wpPostPublishDate)
    {
        $publishDate = $this->dateTime->gmtDate(
            \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT
        );
        if (!empty($wpPostPublishDate)) {
            $publishDateTimestamp = strtotime($wpPostPublishDate);
            $publishDate = $this->dateTime->gmtDate(
                \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT,
                $publishDateTimestamp
            );
        }
        return $publishDate;
    }

    /**
     * Retrieve processed blog post content
     *
     * @param \SimpleXmlElement $post
     * @return string
     */
    private function getPostContent($post)
    {
        $wpPostContent = $this->getWpPostContent($post);
        $processedPostContent = nl2br($this->getProcessedPostContent($wpPostContent));
        return $processedPostContent;
    }

    /**
     * Retrieve WP post content
     *
     * @param \SimpleXmlElement $post
     * @return string
     */
    private function getWpPostContent($post)
    {
        $namespaces = $post->getNamespaces(true);
        $content = $post->children($namespaces['content']);
        return (string)$content->encoded;
    }

    /**
     * Replace media urls
     *
     * @param string $wpPostContent
     * @return string
     */
    private function getProcessedPostContent($wpPostContent)
    {
        $wpBaseUrl = preg_quote($this->wpBaseUrl);
        $mediaUrl = $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        $processedContent = preg_replace(
            '#(src=")' . $wpBaseUrl . '/(wp-content\/uploads)#',
            '${1}' . $mediaUrl . '${2}',
            $wpPostContent
        );

        return $processedContent;
    }

    /**
     * Retrieve category IDs for post
     *
     * @param \SimpleXmlElement $post
     * @return array
     */
    private function getPostCategoryIds($post)
    {
        $categoryElements = $post->xpath('category[@domain="category"]');
        $categories = [];
        foreach ($categoryElements as $category) {
            $categories[] = $this->getCategoryId($category);
        }

        return $categories;
    }

    /**
     * Return category ID with provided url key. New category is created if it does not exist
     *
     * @param \SimpleXmlElement $categoryElement
     * @return string
     */
    private function getCategoryId($categoryElement)
    {
        $categoryId = null;
        $categoryUrlKey = (string)$categoryElement['nicename'];
        try {
            $category = $this->categoryRepository->getByUrlKey($categoryUrlKey);
            $categoryId = $category->getId();
        } catch (NoSuchEntityException $e) {
            $categoryData = [
                'url_key' => $categoryUrlKey,
                'name' => (string)$categoryElement,
                'sort_order' => '999',
                'meta_description' => '',
                'status' => CategoryStatus::DISABLED,
                'store_ids' => [\Magento\Store\Model\Store::DEFAULT_STORE_ID]
            ];

            $categoryDataObject = $this->categoryDataFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $categoryDataObject,
                $categoryData,
                CategoryInterface::class
            );
            $category = $this->categoryRepository->save($categoryDataObject);
            $categoryId = $category->getId();
        }

        return $categoryId;
    }

    /**
     * Retrieve tags for post
     *
     * @param \SimpleXmlElement $post
     * @return array
     */
    private function getPostTags($post)
    {
        $tagElements = $post->xpath('category[@domain="post_tag"]');
        $tags = [];
        foreach ($tagElements as $tag) {
            $tags[] = (string)$tag;
        }

        return $tags;
    }

    /**
     * Get post author id
     *
     * @param \SimpleXmlElement $post
     * @return string
     */
    private function getPostAuthorId($post)
    {
        $namespaces = $post->getNamespaces(true);
        $dcNamespaceItems = $post->children($namespaces['dc']);
        $postCreator = (string)$dcNamespaceItems->creator;
        $authorName = array_key_exists($postCreator, $this->wpAuthors)
            ? $this->wpAuthors[$postCreator]
            : $this->auth->getUser()->getName();

        return $this->authorResolver->resolveIdForWp($authorName);
    }

    /**
     * Get default product conditions for post
     *
     * @return \Aheadworks\Blog\Api\Data\ConditionInterface
     */
    private function getPostDefaultProductConditions()
    {
        $productRule = $this->productRuleFactory->create();
        $arrayForConversion = $productRule->setConditions([])->getConditions()->asArray();
        return $this->conditionConverter->arrayToDataModel($arrayForConversion);
    }

    /**
     * Retrieve ID for post
     *
     * @param \SimpleXmlElement $post
     * @return int|null
     */
    private function getPostId($post)
    {
        $postId = null;
        try {
            $postUrlKey = $this->getPostUrlKey($post);
            $existingPost = $this->postRepository->getByUrlKey($postUrlKey);
            $postId = $existingPost->getId();
        } catch (NoSuchEntityException $e) {
            $postId = null;
        }
        return $postId;
    }
}
