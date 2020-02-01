<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session as ModelSession;
use Magento\Backend\Model\View\Result\Page;
use Magento\Catalog\Helper\Data as HelperData;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\ResourceModel\Profile as ProfileResource;

class Edit extends AbstractProfile
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var LayoutFactory
     */
    protected $_layoutFactory;

    public function __construct(Context $context,
                                Profile $profile,
                                HelperData $catalogHelper,
                                ProfileResource $resource,
                                Registry $registry,
                                LayoutFactory $layoutFactory
    )
    {
        $this->_registry = $registry;
        $this->_layoutFactory = $layoutFactory;

        parent::__construct($context, $profile, $catalogHelper, $resource);
    }

    /**
     * @return Page
     * @throws \Exception
     */
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $model = $this->_profile->load($id)->factory();

        if ($id === 0 || $model->getId()) {
            $data = $this->_getSession()->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            $this->_registry->register('profile_data', $model);
            /** @var Page $page */
            $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
//            $page->getConfig()->setPageLayout('admin-2columns-left');
            $page->addBreadcrumb(__('RapidFlow Profile Manager'), __('RapidFlow Profile Manager'));
            $page->addBreadcrumb(__('New Profile'), __('New Profile'));
            $page->getConfig()->getTitle()->prepend(__('RapidFlow'));
            $profileName = $model->getTitle() ?: __('New');
            $page->getConfig()->getTitle()->prepend($profileName);

//            $layout->getBlock('head')
//                ->setCanLoadExtJs(true)
//                ->setCanLoadRulesJs(true);

//            $page->addContent($page->getLayout()->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit'))
//                ->addLeft($page->getLayout()->createBlock('Unirgy\RapidFlow\Block\Adminhtml\Profile\Edit\Tabs'));
            return $page;
        }
        $this->messageManager->addErrorMessage(__('Profile does not exist'));
        return $this->_redirect('*/*/');
    }
}
