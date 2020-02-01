<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Helper\Strategy;

use BitExpert\ForceCustomerLogin\Model\WhitelistEntry;

/**
 * Class StaticMatcher
 *
 * @package BitExpert\ForceCustomerLogin\Helper\Strategy
 */
class StaticMatcher implements StrategyInterface
{
    /*
     * Rewrite
     */
    const REWRITE_DISABLED_URL_PREFIX = '/index.php';

    /**+
     * @var string
     */
    private $name;

    /**
     * RegExAllMatcher constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function isMatch($url, WhitelistEntry $rule)
    {
        return ($this->getCanonicalUrl($url) === $this->getCanonicalRule($rule->getUrlRule()));
    }

    /**
     * @param string $url
     * @return string
     */
    private function getCanonicalUrl($url)
    {
        $canonicalUrl = rtrim($url, '/') . '/';
        return str_replace(self::REWRITE_DISABLED_URL_PREFIX, '', $canonicalUrl);
    }

    /**
     * @param string $rule
     * @return string
     */
    private function getCanonicalRule($rule)
    {
        return rtrim($rule, '/') . '/';
    }
}
