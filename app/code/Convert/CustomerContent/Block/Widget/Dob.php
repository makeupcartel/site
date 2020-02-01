<?php

namespace Convert\CustomerContent\Block\Widget;

/**
 * Class Dob
 *
 * @package Convert\CustomerContent\Block\Widget
 */
class Dob extends \Magento\Customer\Block\Widget\Dob
{
    /**
     * Returns format which will be applied for DOB in javascript
     *
     * @return string
     */
    public function getDateFormat()
    {
        return str_replace('/','-',$this->_localeDate->getDateFormatWithLongYear());
    }
}
