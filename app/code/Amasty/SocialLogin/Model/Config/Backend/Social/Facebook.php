<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\Config\Backend\Social;

use Amasty\SocialLogin\Model\SocialData;
use Magento\Framework\Exception\LocalizedException;

class Facebook extends \Magento\Framework\App\Config\Value implements
    \Magento\Framework\App\Config\Data\ProcessorInterface
{
    /**
     * @var SocialData
     */
    private $socialData;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Amasty\SocialLogin\Model\SocialData $socialData,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->socialData = $socialData;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function processValue($value)
    {
        return $value;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function save()
    {
        if ($this->getValue() == '1' && strpos($this->socialData->getBaseAuthUrl(), 'http:') !== false) {
            throw new LocalizedException(
                __('Your site is configured to use HTTP connection and Facebook API does not allow it for '
                    . 'login. Please configure HTTPS connection for your site.')
            );
        }

        return parent::save();
    }
}
