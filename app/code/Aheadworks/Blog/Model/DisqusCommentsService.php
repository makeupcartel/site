<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model;

use Aheadworks\Blog\Api\CommentsServiceInterface;
use Aheadworks\Blog\Model\Disqus\Api;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;

/**
 * Disqus comments service
 */
class DisqusCommentsService implements CommentsServiceInterface
{
    /**
     * @var DisqusConfig
     */
    private $disqusConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Api
     */
    private $disqusApi;

    /**
     * @var array
     */
    private $postThreadMap = [];

    /**
     * @var array
     */
    private $threadPostMap = [];

    /**
     * Total number of published comments
     *
     * @var array
     */
    private $commNumPublished = [];

    /**
     * Bundled number of comments by comment statuses
     *
     * @var array
     */
    private $commNumBundleByStatuses = [];

    /**
     * @param DisqusConfig $disqusConfig
     * @param StoreManagerInterface $storeManager
     * @param Api $disqusApi
     */
    public function __construct(
        DisqusConfig $disqusConfig,
        StoreManagerInterface $storeManager,
        Api $disqusApi
    ) {
        $this->disqusConfig = $disqusConfig;
        $this->storeManager = $storeManager;
        $this->disqusApi = $disqusApi;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublishedCommNum($postId, $storeId)
    {
        $key = $postId . '-' . $storeId;
        if (!isset($this->commNumPublished[$key])) {
            $totalCommNum = 0;
            foreach ($this->getForumCodes($storeId) as $forumCode) {
                $responseData = $this->disqusApi->sendRequest(
                    Api::RES_THREADS_DETAILS,
                    [
                        'forum' => $forumCode,
                        'thread:ident' => $postId
                    ]
                );
                if ($responseData) {
                    $totalCommNum += $responseData['posts'];
                }
            }
            $this->commNumPublished[$key] = $totalCommNum;
        }
        return $this->commNumPublished[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPublishedCommNumBundle($postIds, $storeId)
    {
        return $this->getCommNumBundleByStatuses($postIds, $storeId)[Api::POST_STATUS_APPROVED];
    }

    /**
     * {@inheritdoc}
     */
    public function getNewCommNumBundle($postIds, $storeId)
    {
        return $this->getCommNumBundleByStatuses($postIds, $storeId)[Api::POST_STATUS_UNAPPROVED];
    }

    /**
     * {@inheritdoc}
     */
    public function getModerateUrl($websiteId = null)
    {
        $forumCodePart = 'disqus.com';
        if ($forumCode = $this->disqusConfig->getForumCode($websiteId)) {
            $forumCodePart = $forumCode . '.disqus.com';
        }
        return "https://" . $forumCodePart . "/admin/moderate";
    }

    /**
     * Get bundled number of comments by statuses
     *
     * The return value is an associative array keyed by the comment statuses: 'unapproved', 'approved'.
     * The value of each array element is an associative array keyed by the post IDs.
     *
     * @param array $postIds
     * @param int $storeId
     * @return array
     */
    private function getCommNumBundleByStatuses($postIds, $storeId)
    {
        $key = implode('', $postIds) . $storeId;
        if (!isset($this->commNumBundleByStatuses[$key])) {
            $commNumData = $this->getEmptyCommNumBundleData($postIds);

            foreach ($this->getForumCodes($storeId) as $forumCode) {
                $threadPostMap = $this->getPostThreadMap($forumCode, $postIds);
                $responseData = $this->disqusApi->sendRequest(
                    Api::RES_POSTS_LIST,
                    [
                        'forum' => $forumCode,
                        'thread' => array_values($threadPostMap),
                        'related' => [Api::RELATION_THREAD],
                        'include' => [Api::POST_STATUS_APPROVED, Api::POST_STATUS_UNAPPROVED]
                    ]
                );
                if ($responseData) {
                    $this->countCommByStatuses($responseData, $commNumData, $forumCode, $postIds);
                }
            }

            $this->commNumBundleByStatuses[$key] = $commNumData;
        }
        return $this->commNumBundleByStatuses[$key];
    }

    /**
     * @param array $postIds
     * @return array
     */
    private function getEmptyCommNumBundleData(array $postIds)
    {
        $data = [
            Api::POST_STATUS_APPROVED => [],
            Api::POST_STATUS_UNAPPROVED => []
        ];
        foreach ($postIds as $postId) {
            $data[Api::POST_STATUS_APPROVED][$postId] = 0;
            $data[Api::POST_STATUS_UNAPPROVED][$postId] = 0;
        }
        return $data;
    }

    /**
     * @param array $responseData
     * @param array $commData
     * @param string $forumCode
     * @param array $postIds
     * @return void
     */
    private function countCommByStatuses(
        array $responseData,
        array &$commData,
        $forumCode,
        array $postIds
    ) {
        foreach ($responseData as $data) {
            $threadId = $data['thread']['id'];
            $identifiers = isset($this->getThreadPostMap($forumCode, $postIds)[$threadId])
                ? $this->getThreadPostMap($forumCode, $postIds)[$threadId]
                : [];
            $postIdsResult = array_intersect($identifiers, $postIds);
            foreach ($postIdsResult as $postId) {
                if ($data['isApproved']) {
                    $commData[Api::POST_STATUS_APPROVED][$postId]++;
                } else {
                    $commData[Api::POST_STATUS_UNAPPROVED][$postId]++;
                }
            }
        }
    }

    /**
     * Retrieves forum codes for storeId
     *
     * @param int $storeId
     * @return array
     */
    private function getForumCodes($storeId)
    {
        if ($storeId == Store::DEFAULT_STORE_ID) {
            $forumCodes = $this->getForumCodesForAllWebsites();
        } else {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
            $forumCodes = [$this->disqusConfig->getForumCode($websiteId)];
        }
        return $forumCodes;
    }

    /**
     * Retrieves configured forum codes for all websites
     *
     * @return array|null
     */
    private function getForumCodesForAllWebsites()
    {
        $forumCodes = [];
        foreach ($this->storeManager->getWebsites() as $website) {
            $forumCode = $this->disqusConfig->getForumCode($website->getId());
            if (!in_array($forumCode, $forumCodes)) {
                $forumCodes[] = $forumCode;
            }
        }
        return $forumCodes;
    }

    /**
     * Retrieves post to thread data map
     *
     * @param string $forumCode
     * @param array $identifiers
     * @return array
     */
    private function getPostThreadMap($forumCode, $identifiers)
    {
        $key = $forumCode . implode('-', $identifiers);
        if (!isset($this->postThreadMap[$key])) {
            $this->initMaps($forumCode, $identifiers);
        }
        return $this->postThreadMap[$key];
    }

    /**
     * Retrieves thread to post data map
     *
     * @param string $forumCode
     * @param array $identifiers
     * @return array
     */
    private function getThreadPostMap($forumCode, $identifiers)
    {
        $key = $forumCode . implode('-', $identifiers);
        if (!isset($this->threadPostMap[$key])) {
            $this->initMaps($forumCode, $identifiers);
        }
        return $this->threadPostMap[$key];
    }

    /**
     * Init maps
     *
     * @param string $forumCode
     * @param array $identifiers
     * @return void
     */
    private function initMaps($forumCode, $identifiers)
    {
        $key = $forumCode . implode('-', $identifiers);

        $this->postThreadMap[$key] = [];
        $this->threadPostMap[$key] = [];

        $threads = $this->disqusApi->sendRequest(
            Api::RES_FORUMS_LIST_THREADS,
            [
                'forum' => $forumCode,
                'thread:ident' => $identifiers,
                'related' => [Api::RELATION_FORUM],
                'include' => [Api::THREAD_STATUS_OPEN]
            ]
        );
        if ($threads) {
            foreach ($threads as $thread) {
                foreach ($thread['identifiers'] as $threadIdentifiers) {
                    $this->postThreadMap[$key][$threadIdentifiers] = $thread['id'];
                }
                $this->threadPostMap[$key][$thread['id']] = $thread['identifiers'];
            }
        }
    }
}
