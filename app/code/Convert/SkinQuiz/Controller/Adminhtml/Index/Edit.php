<?php

namespace Convert\SkinQuiz\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
	protected $_resultPageFactory;
	
	public function __construct(Context $context, PageFactory $pageFactory)
	{
		parent::__construct($context);
		$this->_resultPageFactory = $pageFactory;
	}

	public function execute()
	{
		//get id tags
		$Id = $this->getRequest()->getParam("id");
		$resultPage=$this->_resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->prepend(__("Detail skin quiz"));
		return $resultPage;
	}

}
