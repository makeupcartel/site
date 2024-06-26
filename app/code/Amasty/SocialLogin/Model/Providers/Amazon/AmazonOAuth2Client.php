<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\Providers\Amazon;

class AmazonOAuth2Client extends \OAuth2Client
{
    /**
     * @param $code
     * @return mixed|\StdClass
     * @throws \Exception
     */
    public function authenticate($code)
    {
        $params = [
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "grant_type" => 'authorization_code',
            "redirect_uri" => $this->redirect_uri,
            "code" => $code,
        ];

        $response = $this->request($this->token_url, http_build_query($params), $this->curl_authenticate_method);

        $response = $this->parseRequestResult($response);

        if (!$response || !isset($response->access_token)) {
            throw new \Exception("The Authorization Service has return: " . $response->error);
        }

        if (isset($response->access_token)) {
            $this->access_token = $response->access_token;
        }

        if (isset($response->refresh_token)) {
            $this->refresh_token = $response->refresh_token;
        }

        if (isset($response->expires_in)) {
            $this->access_token_expires_in = $response->expires_in;
        }

        if (isset($response->expires_in)) {
            $this->access_token_expires_at = time() + $response->expires_in;
        }

        return $response;
    }

    /**
     * @param $url
     * @param bool $params
     * @param string $type
     * @return mixed
     */
    protected function request($url, $params = false, $type = "GET")
    {
        \Hybrid_Logger::info("Enter OAuth2Client::request( $url )");
        \Hybrid_Logger::debug("OAuth2Client::request(). dump request params: ", serialize($params));

        if ($type == "GET") {
            $url = $url . (strpos($url, '?') ? '&' : '?')
                . http_build_query($params, '', '&');
        }

        $this->http_info = [];
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->curl_time_out);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->curl_useragent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->curl_connect_time_out);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->curl_ssl_verifypeer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->curl_ssl_verifyhost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->curl_header);

        if ($this->curl_compressed) {
            curl_setopt($ch, CURLOPT_ENCODING, "gzip,deflate");
        }

        if ($this->curl_proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $this->curl_proxy);
        }

        if ($type == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($params) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
        }
        if ($type == "DELETE") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        }
        if ($type == "PATCH") {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($params) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        }
        $response = curl_exec($ch);
        if ($response === false) {
            \Hybrid_Logger::error("OAuth2Client::request(). curl_exec error: ", curl_error($ch));
        }
        \Hybrid_Logger::debug("OAuth2Client::request(). dump request info: ", serialize(curl_getinfo($ch)));
        \Hybrid_Logger::debug("OAuth2Client::request(). dump request result: ", serialize($response));

        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ch));

        curl_close($ch);

        return $response;
    }

    /**
     * @param $result
     * @return mixed|\StdClass
     */
    protected function parseRequestResult($result)
    {
        if (json_decode($result)) {
            return json_decode($result);
        }

        parse_str($result, $output);
        $result = new \StdClass();

        foreach ($output as $k => $v) {
            $result->$k = $v;
        }

        return $result;
    }
}
