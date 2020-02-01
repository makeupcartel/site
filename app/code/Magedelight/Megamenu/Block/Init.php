<?php

namespace Magedelight\Megamenu\Block;

class Init extends \Magento\Backend\Block\AbstractBlock
{

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\View\Page\Config $pageConfig,
        array $data = []
    ) {
        $this->pageConfig = $pageConfig;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $page = $this->pageConfig;
        $page->addPageAsset('Magedelight_Megamenu::css/font-awesome/css/font-awesome.min.css');
        $page->addPageAsset('Magedelight_Megamenu::js/megamenu/megamenu.js');
    }
}
