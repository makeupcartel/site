<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Disqus;

use Aheadworks\Blog\Model\DisqusConfig;
use Magento\Framework\HTTP\Adapter\CurlFactory;

/**
 * Disqus Api
 * @package Aheadworks\Blog\Model\Disqus
 */
class Api
{
    /**
     * Api resources
     */
    const RES_FORUMS_LIST_THREADS = 'forums/listThreads';
    const RES_POSTS_LIST = 'posts/list';
    const RES_THREADS_DETAILS = 'threads/details';

    /**
     * Thread statuses
     */
    const THREAD_STATUS_OPEN = 'open';
    const THREAD_STATUS_CLOSED = 'closed';
    const THREAD_STATUS_KILLED = 'killed';

    /**
     * Post statuses
     */
    const POST_STATUS_UNAPPROVED = 'unapproved';
    const POST_STATUS_APPROVED = 'approved';
    const POST_STATUS_SPAM = 'spam';
    const POST_STATUS_DELETED = 'deleted';
    const POST_STATUS_FLAGGED = 'flagged';
    const POST_STATUS_HIGHLIGHTED = 'highlighted';

    /**
     * Response relations
     */
    const RELATION_FORUM = 'forum';
    const RELATION_THREAD = 'thread';
    const RELATION_AUTHOR = 'author';

    /**
     * API version
     */
    const VERSION = '3.0';

    /**
     * Request method
     */
    const METHOD = \Zend_Http_Client::GET;

    /**
     * Output type
     */
    const OUTPUT_TYPE = 'json';

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var DisqusConfig
     */
    private $disqusConfig;

    /**
     * @param CurlFactory $curlFactory
     * @param DisqusConfig $disqusConfig
     */
    public function __construct(CurlFactory $curlFactory, DisqusConfig $disqusConfig)
    {
        $this->curlFactory = $curlFactory;
        $this->disqusConfig = $disqusConfig;
    }

    /**
     * Send request
     *
     * @param string $resource
     * @param array $args
     * @return array|bool
     */
    public function sendRequest($resource, $args = [])
    {
        /** @var \Magento\Framework\HTTP\Adapter\Curl $curl */
        $curl = $this->curlFactory->create();
        $curl->setConfig(['timeout' => 60, 'header' => false]);
        $curl->write(self::METHOD, $this->getEndpoint($resource, $args));
        try {
            $response = \Zend_Json::decode($curl->read());
            $response = $response['code'] != 0 ? false : $response['response'];
        } catch (\Exception $e) {
            $response = false;
        }
        $curl->close();
        return $response;
    }

    /**
     * Get prepared endpoint url
     *
     * @param string $resource
     * @param array $args
     * @return string
     */
    protected function getEndpoint($resource, $args = [])
    {
        $endpoint = 'https://disqus.com/api/' . self::VERSION . '/' .
            $resource . '.' . self::OUTPUT_TYPE;
        $rawParams = array_merge(
            ['api_secret' => $this->disqusConfig->getSecretKey(null)],
            $args
        ); // todo: store ID

        $params = [];
        foreach ($rawParams as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $params[] = $key . '[]=' . urlencode($item);
                }
            } else {
                $params[] = $key . '=' . urlencode($value);
            }
        }
        $endpoint .= '?' . implode('&', $params);

        return $endpoint;
    }
}
