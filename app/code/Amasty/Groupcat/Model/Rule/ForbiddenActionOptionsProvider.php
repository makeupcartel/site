<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */

namespace Amasty\Groupcat\Model\Rule;

class ForbiddenActionOptionsProvider implements \Magento\Framework\Data\OptionSourceInterface
{
    const REDIRECT_TO_404 = 0;
    const REDIRECT_TO_PAGE = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::REDIRECT_TO_404, 'label' => __('Show 404 page')],
            ['value' => self::REDIRECT_TO_PAGE, 'label' => __('Redirect to CMS page')]
        ];
    }
}
