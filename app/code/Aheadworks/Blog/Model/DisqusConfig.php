<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Disqus config
 */
class DisqusConfig
{
    /**
     * Configuration path to Disqus forum code
     */
    const XML_PATH_DISQUS_FORUM_CODE = 'aw_blog/general/disqus_forum_code';

    /**
     * Configuration path to Disqus secret API key
     */
    const XML_PATH_DISQUS_SECRET_KEY = 'aw_blog/general/disqus_secret_key';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get forum code
     *
     * @param int|null $websiteId
     * @return string
     */
    public function getForumCode($websiteId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DISQUS_FORUM_CODE,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get secret API key
     *
     * @param int $websiteId
     * @return string
     */
    public function getSecretKey($websiteId)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DISQUS_SECRET_KEY,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
