<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Model\Config\Backend;

use Magento\Framework\Exception\LocalizedException;

class Social extends \Magento\Framework\App\Config\Value implements
    \Magento\Framework\App\Config\Data\ProcessorInterface
{
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
        if ($this->getValue() == '1' && !class_exists('Hybrid_Auth')) {
            throw new LocalizedException(
                __('Additional Social Login package is not installed. '
                    . 'Please, run the following command in the SSH: composer require hybridauth/hybridauth 2.13.*')
            );
        }

        return parent::save();
    }
}
