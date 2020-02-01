<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\RapidFlow\Model\Source;

use Magento\Framework\DataObject;

abstract class AbstractSource extends DataObject
{
    protected $_translateModule = 'Unirgy_RapidFlow';

    abstract public function toOptionHash($selector = false);

    public function toOptionArray($selector = false)
    {
        $arr = [];
        foreach ($this->toOptionHash($selector) as $v => $l) {
            if (!is_array($l)) {
                $arr[] = ['label' => $l, 'value' => $v];
            } else {
                $options = [];
                foreach ($l as $v1 => $l1) {
                    $options[] = ['value' => $v1, 'label' => $l1];
                }
                $arr[] = ['label' => $v, 'value' => $options];
            }
        }
        return $arr;
    }
}
