<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLogin
 */


namespace Amasty\SocialLogin\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class RedirectUrl extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Amasty\SocialLogin\Model\SocialData
     */
    private $socialData;

    public function __construct(
        Context $context,
        \Amasty\SocialLogin\Model\SocialData $socialData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->socialData = $socialData;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $elementId   = explode('_', $element->getHtmlId());
        switch ($elementId[1]) {
            case "twitter":
                $redirectUrl = $this->socialData->getRedirectUrl($elementId[1]);
                $redirectUrl = explode('?', $redirectUrl);
                $redirectUrl = $redirectUrl[0];
                break;
            case "instagram":
                $redirectUrl = $this->getInstagramRedirectUrl($elementId);
                break;
            default:
                $redirectUrl = $this->socialData->getRedirectUrl($elementId[1]);
        }

        $html = $this->getFieldTemplate($element, $elementId, $redirectUrl);

        return $html;
    }

    /**
     * @param $element
     * @param $elementId
     * @param $redirectUrl
     * @return string
     */
    private function getFieldTemplate($element, $elementId, $redirectUrl)
    {
        if ($elementId[1] == 'instagram') {
            $html = '<textarea style="opacity:1;" readonly id="%s" class="input-text admin__control-text"
                        onclick="this.select()" type="text">%s</textarea>';
        } else {
            $html = '<input style="opacity:1;" readonly id="%s" class="input-text admin__control-text"
                        value="%s" onclick="this.select()" type="text">';
        }

        return sprintf($html, $element->getHtmlId(), $redirectUrl);
    }

    /**
     * @param $elementId
     * @return string
     */
    private function getInstagramRedirectUrl($elementId)
    {
        $redirectUrl = $this->socialData->getRedirectUrl($elementId[1]);

        if (strpos($redirectUrl, 'https:') !== false) {
            $redirectUrl .= "\n" . str_replace('https:', 'http:', $redirectUrl);
        }

        return $redirectUrl;
    }
}
