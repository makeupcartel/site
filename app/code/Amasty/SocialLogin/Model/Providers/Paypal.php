<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\Providers;

use Magento\Framework\App\ObjectManager;
use Amasty\SocialLogin\Model\Providers\Paypal\PaypalOAuth2Client;

class Paypal extends \Hybrid_Provider_Model_OAuth2
{
    /**
     * @var string
     */
    public $scope = 'openid profile email';

    /**
     * IDp wrappers initializer
     * @throws \Exception
     */
    public function initialize()
    {
        if (!$this->config['keys']['id'] || !$this->config['keys']['secret']) {
            throw new \Exception("Your application id and secret are required in order to connect to {$this->providerId}.", 4);
        }

        if (isset($this->config['scope']) && !empty($this->config['scope'])) {
            $this->scope = $this->config['scope'];
        }

        $this->api = ObjectManager::getInstance()->create(PaypalOAuth2Client::class, [
            'client_id' => $this->config['keys']['id'],
            'client_secret' => $this->config['keys']['secret'],
            'redirect_uri' => $this->endpoint, 'compressed' => $this->compressed
        ]);

        $this->api->api_base_url = 'https://api.paypal.com';
        $this->api->authorize_url = 'https://www.paypal.com/signin/authorize';
        $this->api->token_url = 'https://api.paypal.com/v1/oauth2/token';

        $this->api->curl_header = ['Content-Type: application/x-www-form-urlencoded'];

        if ($this->token('access_token')) {
            $this->api->access_token = $this->token('access_token');
            $this->api->refresh_token = $this->token('refresh_token');
            $this->api->access_token_expires_in = $this->token('expires_in');
            $this->api->access_token_expires_at = $this->token('expires_at');
        }

        if (isset(\Hybrid_Auth::$config['proxy'])) {
            $this->api->curl_proxy = \Hybrid_Auth::$config['proxy'];
        }
    }

    /**
     * load the user profile from the IDp api client
     * @return \Hybrid_User_Profile
     * @throws \Exception
     */
    public function getUserProfile()
    {
        $data = $this->api->get('https://api.paypal.com/v1/oauth2/token/userinfo?schema=openid');
        if (!isset($data->user_id)) {
            throw new \Exception("User profile request failed! {$this->providerId} returned an invalid response.", 6);
        }

        $this->user->profile->identifier = @$data->user_id;
        $this->user->profile->email = @$data->email;
        $this->user->profile->displayName = @$data->name;
        $this->user->profile->zip = @$data->postal_code;

        return $this->user->profile;
    }
}
