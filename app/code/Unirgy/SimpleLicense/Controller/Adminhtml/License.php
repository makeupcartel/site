<?php

namespace Unirgy\SimpleLicense\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


abstract class License extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(Context $context)
    {
//        $this->resultPageFactory = $pageFactory;

        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Unirgy_SimpleUp::stores_tools');
    }
}
