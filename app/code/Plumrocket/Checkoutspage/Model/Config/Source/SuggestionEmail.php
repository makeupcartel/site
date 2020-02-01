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

class SuggestionEmail extends Suggestion
{

    /**
     * Options getter
     *
     * @return Array
     */
    public function toOptionArray()
    {
        $array = parent::toOptionArray();
        foreach ($array as $key => $item) {
            if ($item['value'] == parent::SUGGESTION_TYPE_RECENTLY_VIEWED) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Get options in "key-value" format
     *
     * @return Array
     */
    public function toArray()
    {
        $array = parent::toArray();
        unset($array[parent::SUGGESTION_TYPE_RECENTLY_VIEWED]);
        return $array;
    }
}
