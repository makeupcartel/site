<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Groupcat
 */


namespace Amasty\Groupcat\Block\Framework\Pricing\Request;

class Content extends \Magento\Backend\Block\Template
{
    /**
     * @var \Amasty\Groupcat\Helper\Data
     */
    private $helper;

    /**
     * Content constructor.
     *
     * @param \Amasty\Groupcat\Helper\Data            $helper
     * @param \Magento\Backend\Block\Template\Context $context
     */
    public function __construct(
        \Amasty\Groupcat\Helper\Data $helper,
        \Magento\Backend\Block\Template\Context $context
    ) {
        parent::__construct($context);
        $this->helper = $helper;
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'Amasty_Groupcat::price/request/content.phtml';
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if ($this->getText() || $this->getImageUrl()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Content text of popup link
     *
     * @return string
     */
    public function getText()
    {
        return $this->helper->getModuleStoreConfig('frontend/text');
    }

    /**
     * Content Image Source
     *
     * @return string
     */
    public function getImageUrl()
    {
        if (!$this->hasData('image_url')) {
            $imageUrl = '';
            $image = $this->helper->getModuleStoreConfig('frontend/image');
            if ($image) {
                $imageUrl = $this->getUrl(
                    '',
                    [
                        '_type'   => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA,
                        '_direct' => 'amasty/hide_price/' . $image
                    ]
                );
            }

            $this->setData('image_url', $imageUrl);
        }

        return $this->_getData('image_url');
    }
}
