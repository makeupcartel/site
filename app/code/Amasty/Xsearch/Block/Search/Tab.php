<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xsearch
 */


namespace Amasty\Xsearch\Block\Search;

use Magento\Framework\View\Element\Template;

class Tab extends \Magento\Framework\View\Element\Template
{
    /**
     * @var array
     */
    private $tabs = [];

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Amasty\Xsearch\Helper\Data
     */
    private $helper;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Amasty\Xsearch\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
        $this->helper = $helper;
    }

    /**
     * @param $tabName
     * @param $blockName
     * @param $blockClass
     * @param $template
     * @return bool
     */
    public function addTab($tabName, $blockName, $blockClass, $template)
    {
        if (!class_exists($blockClass)) {
            return false;
        }

        if (strpos($blockClass, 'Landing') !== false
            && !$this->moduleManager->isEnabled('Amasty_Xlanding')
        ) {
            return false;
        }

        $this->tabs[] = [
            'name' => $tabName,
            'block_name' => $blockName,
            'block_class' => $blockClass,
            'template' => $template
        ];
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTabs()
    {
        foreach ($this->tabs as $index => $tab) {
            $block = $this->getLayout()->createBlock($tab['block_class'], $tab['block_name']);
            $html = $block ? $block->setTemplate($tab['template'])->toHtml() : '';
            $itemsCount = $block && method_exists($block, 'getLoadedSearchCollection') ?
                $block->getLoadedSearchCollection()->getSize()
                : '';
            $itemsCount = $itemsCount ? '(' . $itemsCount . ')' : '';

            $this->tabs[$index]['html'] = $html;
            $this->tabs[$index]['items_count'] = $itemsCount;
        }

        return $this->tabs;
    }

    /**
     * @return bool
     */
    public function isTabsEnabled()
    {
        return (bool)$this->helper->getModuleConfig('general/enable_tabs_search_result');
    }

    /**
     * @return string
     */
    public function getProductCount()
    {
        $result = '';
        $block = $this->getChildBlock('search.result');
        if ($block) {
            $count = $block->getResultCount();
            if ($count) {
                $result = '(' . $count . ')';
            }
        }

        return $result;
    }
}
