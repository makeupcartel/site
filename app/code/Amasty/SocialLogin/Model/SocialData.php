<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model;

class SocialData
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigData
     */
    private $configData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Url $urlBuilder,
        \Amasty\SocialLogin\Model\ConfigData $configData
    ) {
        $this->storeManager = $storeManager;
        $this->configData = $configData;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function getEnabledSocials()
    {
        $socials = [];
        foreach ($this->getAllSocialTypes() as $type => $label) {
            if ($this->configData->getConfigValue($type . '/enabled')
                && $this->configData->getConfigValue($type . '/api_key')
                && $this->configData->getConfigValue($type . '/api_secret')
            ) {
                $sortOrder = $this->configData->getConfigValue($type . '/sort_order');
                /* when two socials have one sort order*/
                while (true) {
                    if (array_key_exists($sortOrder, $socials)) {
                        $sortOrder++;
                    } else {
                        break;
                    }
                }

                $socials[$sortOrder] = [
                    'type' => $type,
                    'label' => $label,
                    'url' => $this->urlBuilder->getUrl('amsociallogin/social/login', [
                        'type' => $type,
                        '_secure' => true
                    ])
                ];
            }
        }
        ksort($socials);

        return $socials;
    }

    /**
     * @return array
     */
    public function getAllSocialTypes()
    {
        return [
            'google' => 'Google',
            'facebook' => 'Facebook',
            'twitter' => 'Twitter',
            'linkedin' => 'LinkedIn',
            'instagram' => 'Instagram',
            'github' => 'Github',
            'amazon' => 'Amazon',
            'paypal' => 'Paypal',
        ];
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBaseAuthUrl()
    {
        $store = $this->storeManager->getStore();

        return $this->urlBuilder->getUrl('amsociallogin/social/callback', [
            '_nosid'  => true,
            '_scope'  => $store->getId(),
            '_secure' => $store->isUrlSecure()
        ]);
    }

    /**
     * @param $socialKey
     * @param array $params
     * @return string
     */
    public function getLoginUrl($socialKey, $params = [])
    {
        $params['type'] = $socialKey;

        return $this->urlBuilder->getUrl('amsociallogin/social/login', $params);
    }

    /**
     * @param $type
     * @return array
     */
    public function getSocialConfig($type)
    {
        $result = [];
        $apiData = [
            'facebook' => ["trustForwarded" => false, 'scope' => 'email, public_profile'],
            'twitter' => ["includeEmail" => true],
            'instagram' => ['wrapper' => ['class' => \Amasty\SocialLogin\Model\Providers\Instagram::class]],
            'linkedin' => ["fields" => ['id', 'first-name', 'last-name', 'email-address']],
            'google' => [],
            'amazon' => ['wrapper' => ['class' => \Amasty\SocialLogin\Model\Providers\Amazon::class]],
            'paypal' => [
                'wrapper' => ['class' => \Amasty\SocialLogin\Model\Providers\Paypal::class],
                'scope'   => 'openid profile email'
            ],
        ];

        if ($type && array_key_exists($type, $apiData)) {
            $result = $apiData[$type];
        }

        return $result;
    }

    /**
     * @param $type
     * @return string
     */
    public function getRedirectUrl($type)
    {
        $authUrl = $this->getBaseAuthUrl();
        $allSociaTypes = $this->getAllSocialTypes();
        $type = $allSociaTypes[$type];

        switch ($type) {
            case 'Facebook':
                $param = 'hauth_done=' . $type;
                break;
            default:
                $param = 'hauth.done=' . $type;
        }

        return $authUrl . ($param ? (strpos($authUrl, '?') ? '&' : '?') . $param : '');
    }
}
