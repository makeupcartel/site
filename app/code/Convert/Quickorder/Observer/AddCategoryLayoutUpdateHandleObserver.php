<?php

namespace Convert\Quickorder\Observer;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout as Layout;
use Magento\Framework\View\Layout\ProcessorInterface as LayoutProcessor;

/**
 *  AddCategoryLayoutUpdateHandleObserver
 */
class AddCategoryLayoutUpdateHandleObserver implements ObserverInterface
{
    /**
     * Category Custom Layout Name
     *
     * It's the filename of layout phisically located
     * at `Makeupcartel/Quickorder/view/frontend/layout/catalog_category_view_custom_layout.xml`
     */
    const LAYOUT_HANDLE_NAME = 'catalog_category_view_custom_layout';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Convert\Quickorder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * AddCategoryLayoutUpdateHandleObserver constructor.
     * @param Registry $registry
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Convert\Quickorder\Helper\Data $helper
     * @param \Magento\Framework\Controller\ResultFactory $result
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     */
    public function __construct(
        Registry $registry,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Request\Http $request,
        \Convert\Quickorder\Helper\Data $helper,
        \Magento\Framework\Controller\ResultFactory $result,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->registry = $registry;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->request = $request;
        $this->helper = $helper;
        $this->resultRedirect = $result;
        $this->redirect = $redirect;
    }

    /**
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var Event $event */
        $event = $observer->getEvent();
        $actionName = $event->getData('full_action_name');
        /** @var CategoryModel|null $category **/
        $category = $this->registry->registry('current_category');
        $route      = $this->request->getRouteName();
        $action     = $this->request->getActionName();

        if ($route == 'cvquickorder' && $action == 'index') {
            if (!$this->helper->isLoggedIn()) {
                $url = $this->url->getUrl('cms/noroute/index');
                $this->responseFactory->create()->setRedirect($url)->sendResponse();
                die();
            }
        }
    }
}
