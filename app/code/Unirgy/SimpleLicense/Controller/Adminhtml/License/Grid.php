<?php

namespace Unirgy\SimpleLicense\Controller\Adminhtml\License;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Unirgy\SimpleLicense\Controller\Adminhtml\AbstractLicense;
use Unirgy\SimpleLicense\Controller\Adminhtml\License;


class Grid extends AbstractLicense
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var LayoutFactory
     */
    protected $_frameworkViewLayoutFactory;

    public function __construct(Context $context,
                                PageFactory $resultPageFactory,
        LayoutFactory $frameworkViewLayoutFactory)
    {
        $this->pageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $page->getLayout()->addBlock(
            $this->_frameworkViewLayoutFactory->create()->createBlock(''), '', 'page.main.container'
        );
    }
}
