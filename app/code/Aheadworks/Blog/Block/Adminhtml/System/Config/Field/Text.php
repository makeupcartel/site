<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Block\Adminhtml\System\Config\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Add placeholder to text field
 */
class Text extends Field
{
    const TWITTER_SITE_FIELD_ID = 'aw_blog_general_twitter_site';
    const TWITTER_CREATOR_FIELD_ID = 'aw_blog_general_twitter_creator';
    const TWITTER_SITE_FIELD_PLACEHOLDER = '@yoursiteontwitter';
    const TWITTER_CREATOR_FIELD_PLACEHOLDER = '@authorontwitter';

    /**
     * Insert placeholder attribute to input element
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $fieldHtml = parent::_getElementHtml($element);

        if (($typeAttrPosition = strpos($fieldHtml, 'type=')) !== false) {
            $placeholderText = '';
            switch ($element->getId()) {
                case self::TWITTER_SITE_FIELD_ID:
                    $placeholderText = self::TWITTER_SITE_FIELD_PLACEHOLDER;
                    break;
                case self::TWITTER_CREATOR_FIELD_ID:
                    $placeholderText = self::TWITTER_CREATOR_FIELD_PLACEHOLDER;
                    break;
            }
            $placeholderAttr = "placeholder='" . $placeholderText . "' ";
            $fieldHtml = substr_replace($fieldHtml, $placeholderAttr, $typeAttrPosition, 0);
        }

        return $fieldHtml;
    }
}
