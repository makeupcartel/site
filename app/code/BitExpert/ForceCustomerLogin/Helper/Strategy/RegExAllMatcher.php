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
 * Class RegExAllMatcher
 *
 * @package BitExpert\ForceCustomerLogin\Helper\Strategy
 */
class RegExAllMatcher implements StrategyInterface
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
        return (
            preg_match(
                sprintf(
                    '#^.*%s/?.*$#i',
                    $this->quoteRule($rule->getUrlRule())
                ),
                $this->getCleanUrl($url)
            ) === 1
        );
    }

    /**
     * Quote delimiter in whitelist entry rule
     *
     * @param string $rule
     * @param string $delimiter
     * @return string
     */
    private function quoteRule($rule, $delimiter = '#')
    {
        return str_replace($delimiter, \sprintf('\%s', $delimiter), $rule);
    }

    /**
     * @param $url
     * @return string
     */
    private function getCleanUrl($url)
    {
        return str_replace(self::REWRITE_DISABLED_URL_PREFIX, '', $url);
    }
}
