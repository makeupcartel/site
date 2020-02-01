<?php

namespace Convert\BookSkin\Controller\IndexBook;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Convert\BookSkin\Controller\IndexBook
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
	protected $_pageFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     */
	public function __construct(
		Context $context,
		PageFactory $pageFactory
	)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
	public function execute()
	{
		return $this->_pageFactory->create();
	}
}