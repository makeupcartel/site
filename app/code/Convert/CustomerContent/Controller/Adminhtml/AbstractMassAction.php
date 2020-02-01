<?php

namespace Convert\CustomerContent\Controller\Adminhtml;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

abstract class AbstractMassAction extends \Magento\Backend\App\Action
{
    /**
     * @var string
     */
    protected $redirectUrl = '*/*/';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var model
     */
    protected $model;

    /**
     * @param Context $context
     * @param Filter $filter
     */
    public function __construct(Context $context, Filter $filter)
    {
        parent::__construct($context);
        $this->filter = $filter;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * Return component referer url
     * TODO: Technical dept referer url should be implement as a part of Action configuration in in appropriate way
     *
     * @return null|string
     */
    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl() ?: $this->redirectUrl;
    }

    /**
     * Execute action to collection items
     * @return ResponseInterface|ResultInterface
     */
    abstract protected function massAction($collection);
}
