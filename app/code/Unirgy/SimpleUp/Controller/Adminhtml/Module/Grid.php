<?php

namespace Unirgy\SimpleUp\Controller\Adminhtml\Module;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Unirgy\SimpleUp\Controller\Adminhtml\AbstractModule;


class Grid extends AbstractModule
{
    public function __construct(Context $context,
                                PageFactory $pageFactory)
    {
        parent::__construct($context, $pageFactory);
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $block = $page->getLayout()->addBlock('\Unirgy\SimpleUp\Block\Adminhtml\Module\Grid', 'usimpleup.module.grid', 'page.main.container');

        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response->setBody($block->toHtml());
    }
}
