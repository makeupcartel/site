<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model;

use Amasty\SocialLogin\Api\Data\SocialInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel;

class Social extends \Magento\Framework\Model\AbstractModel implements SocialInterface
{
    /**
     * @var SocialData
     */
    private $socialData;

    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var array
     */
    private $allSocialTypes;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\SocialLogin\Model\SocialData $socialData,
        \Amasty\SocialLogin\Model\ConfigData $configData,
        ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->socialData = $socialData;
        $this->configData = $configData;
        $this->allSocialTypes = $socialData->getAllSocialTypes();
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(\Amasty\SocialLogin\Model\ResourceModel\Social::class);
    }

    /**
     * @param $type
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getUserProfile($type)
    {
        if (!class_exists('Hybrid_Auth')) {
            throw new LocalizedException(
                __('Additional Social Login package is not installed. '
                    . 'Please, run the following command in the SSH: composer require hybridauth/hybridauth 2.13.*')
            );
        }

        $config = [
            "base_url" => $this->socialData->getBaseAuthUrl(),
            "providers" => [$this->allSocialTypes[$type] => $this->getProviderData($type)],
            "debug_mode" => false
        ];
        $auth = new \Hybrid_Auth($config);
        $adapter = $auth->authenticate($this->allSocialTypes[$type], null);

        return $adapter->getUserProfile();
    }

    /**
     * @return array
     */
    public function getProviderData($type)
    {
        $apiKey = $this->configData->getConfigValue($type . '/api_key');
        $apiSecret = $this->configData->getConfigValue($type . '/api_secret');
        $data = [
            "enabled" => true,
            "keys" => ['id' => $apiKey, 'key' => $apiKey, 'secret' => $apiSecret]
        ];

        return array_merge($data, $this->socialData->getSocialConfig($type));
    }

    /**
     * @inheritdoc
     */
    public function getSocialId()
    {
        return $this->_getData(SocialInterface::SOCIAL_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSocialId($socialId)
    {
        $this->setData(SocialInterface::SOCIAL_ID, $socialId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(SocialInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(SocialInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->_getData(SocialInterface::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->setData(SocialInterface::TYPE, $type);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_getData(SocialInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(SocialInterface::NAME, $name);

        return $this;
    }
}
