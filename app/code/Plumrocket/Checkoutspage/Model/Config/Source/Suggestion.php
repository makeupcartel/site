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

namespace Plumrocket\Checkoutspage\Model\Config\Source;

class Suggestion implements \Magento\Framework\Option\ArrayInterface
{

    const SUGGESTION_TYPE_CROSSSEL = 'CrossSell';
    const SUGGESTION_TYPE_UPSELL = 'UpSell';
    const SUGGESTION_TYPE_RELATED = 'Related';
    const SUGGESTION_TYPE_RECENTLY_VIEWED = 'RecentlyViewed';

    /**
     * Options getter
     *
     * @return Array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SUGGESTION_TYPE_CROSSSEL, 'label' => __('Cross-sells Products')],
            ['value' => self::SUGGESTION_TYPE_UPSELL, 'label' => __('Up-sells Products')],
            ['value' => self::SUGGESTION_TYPE_RELATED, 'label' => __('Related Products')],
            ['value' => self::SUGGESTION_TYPE_RECENTLY_VIEWED, 'label' => __('Recently Viewed Products')],

        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return Array
     */
    public function toArray()
    {
        return [
            self::SUGGESTION_TYPE_CROSSSEL => __('Cross-sells Products'),
            self::SUGGESTION_TYPE_UPSELL => __('Up-sells Products'),
            self::SUGGESTION_TYPE_RELATED => __('Related Products'),
            self::SUGGESTION_TYPE_RECENTLY_VIEWED => __('Recently Viewed Products'),
        ];
    }
}
