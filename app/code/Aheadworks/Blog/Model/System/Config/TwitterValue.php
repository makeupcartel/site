<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\System\Config;

use Magento\Framework\App\Config\Value;

/**
 * Class TwitterValue
 * @package Aheadworks\Blog\Model\System\Config
 */
class TwitterValue extends Value
{
    const AT_CHARACTER = '@';

    /**
     * Check and insert @ character at the beginning of value
     *
     * @return void
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if ($value && ($value[0] != self::AT_CHARACTER)) {
            $value = self::AT_CHARACTER . $value;
        }

        $this->setValue($value);
    }
}
