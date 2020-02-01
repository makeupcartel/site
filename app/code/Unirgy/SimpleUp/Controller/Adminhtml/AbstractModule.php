<?php

namespace Unirgy\SimpleUp\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Module
 * @method Http getRequest()
 * @package Unirgy\SimpleUp\Controller\Adminhtml
 */
abstract class AbstractModule extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(Context $context,
                                PageFactory $pageFactory)
    {
        $this->resultPageFactory = $pageFactory;

        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Unirgy_SimpleUp::stores_tools');
    }
}
