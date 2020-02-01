<?php

namespace Convert\SkinQuiz\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
	protected $_resultPageFactory;
	
	public function __construct(Context $context, PageFactory $pageFactory)
	{
		parent::__construct($context);
		$this->_resultPageFactory = $pageFactory;
	}

	public function execute()
	{
		$resultPage=$this->_resultPageFactory->create();
		$resultPage->getConfig()->getTitle()->prepend(__("List quizs for skin"));
		return $resultPage;
	}

}
