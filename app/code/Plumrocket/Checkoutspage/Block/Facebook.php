<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Checkoutspage v2.x.x
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\Checkoutspage\Block;

class Facebook extends Page
{

	/**
	 * is enabled
	 * @return boolean
	 */
	public function isEnabled()
	{
		return $this->getSettings('facebook/enabled');
	}

	/**
	 * get facebook url
	 * @return string url
	 */
	public function getFacebookUrl()
	{
		$url = $this->getSettings('facebook/url');
		$_url = parse_url($url);
        if (!isset($_url['scheme'])) {
            $url = 'http://' . $url;
        }
        return $url;
	}
}