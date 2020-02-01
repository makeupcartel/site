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

namespace Plumrocket\Checkoutspage\Block;

class Cms extends Page
{
    /**
     * @var Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * @param \Plumrocket\Checkoutspage\Helper\Data            $helper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider                     $filterProvider
     * @param Array                                            $data
     */
    public function __construct(
        \Plumrocket\Checkoutspage\Helper\Data $helper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = []
    ) {
        parent::__construct($helper, $context, $data);
        $this->_filterProvider = $filterProvider;
    }

    /**
     * Is enabled
     * @param sting $location right|bottom
     * @return boolean
     */
    public function isEnabled($location)
    {
        if (!$this->_helper->moduleEnabled()) {
            return false;
        }

        return $this->_scopeConfig->getValue(
            \Plumrocket\Checkoutspage\Helper\Data::$configSectionId . '/cms_' . $location . '/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get right cms content
     * @return string
     */
    public function getRightCmsContent()
    {
        return $this->_getContent(
            $this->_scopeConfig->getValue(
                \Plumrocket\Checkoutspage\Helper\Data::$configSectionId . '/cms_right/content',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }

    /**
     * Filter wysiwyg content
     * @param  string $content
     * @return string
     */
    protected function _getContent($content)
    {
        return $this->_filterProvider->getBlockFilter()->filter($content);
    }


    /**
     * Get bottom cms content
     * @return string
     */
    public function getBottomCmsContent()
    {
        return $this->_getContent(
            $this->_scopeConfig->getValue(
                \Plumrocket\Checkoutspage\Helper\Data::$configSectionId . '/cms_bottom/content',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }
}
