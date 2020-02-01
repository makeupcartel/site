<?php

namespace Convert\SkinQuiz\Controller\Quiz;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Convert\SkinQuiz\Model\Filter;

class Result extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{
	protected $_pageFactory;

	protected $_filter;

	public function __construct(
		Context $context,
		PageFactory $pageFactory,
		Filter $filter
	)
	{
		$this->_pageFactory = $pageFactory;
		$this->_filter = $filter;
		return parent::__construct($context);
	}

	public function execute()
	{
		$params = $this->getRequest()->getParams();

		$resultPage = $this->_pageFactory->create();

		$resultPage->getLayout()->getBlock('skin_quiz_result')
			->setResultCollection($this->_filter->getResult($params))
			->setSkinTypeTitle($this->_filter->getSkinTypeTitle($params))
			->setSkinTypeResult($this->_filter->getSkinTypeResult($params));

		return $resultPage;
	}
}
