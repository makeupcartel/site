<?php

namespace Convert\CustomerContent\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Convert_CustomerContent::menu_top_category';

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * @var \Convert\CustomerContent\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Convert\CustomerContent\Model\CategoryStoreFactory
     */
    protected $categoryStoreFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Convert\CustomerContent\Model\CategoryFactory $categoryFactory
     * @param \Convert\CustomerContent\Model\CategoryStoreFactory $categoryStoreFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Backend\Helper\Js $jsHelper,
        \Convert\CustomerContent\Model\CategoryFactory $categoryFactory,
        \Convert\CustomerContent\Model\CategoryStoreFactory $categoryStoreFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->jsHelper = $jsHelper;
        $this->cacheTypeList = $cacheTypeList;
        $this->categoryFactory = $categoryFactory;
        $this->categoryStoreFactory = $categoryStoreFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Convert\CustomerContent\Model\Category $model */
            $model = $this->categoryFactory->create();
            $id = $this->getRequest()->getParam('category_id');
            if ($id) {
                $model->load($id);
            }
            $ids = '';
            if (isset($data['store_id']) && $data['store_id'][0] == 0) {
                $storeManagerDataList = $this->_storeManager->getStores();
                $storeIDs = [];
                foreach ($storeManagerDataList as $value) {
                    $storeIDs[] = $value->getId();
                }
                $ids = implode(',', $storeIDs);
            }
            $storeId = implode(',', $data['store_id']);
            $model->setData('title', $data['title']);
            $txtGenerate = $this->generateRandomString();
            $model->setData('url', preg_replace('/[^a-zA-Z0-9_ -%][().][\/]/s', '', $data['title']).$txtGenerate);
            $model->setData('status', $data['status']);
            $model->setData('type', $data['type']);
            $model->setData('description', $data['description']);
            $model->setData('store_id', $ids ? $ids : $storeId);

            $this->_eventManager->dispatch(
                'ccc_category_prepare_save',
                ['category' => $model, 'request' => $this->getRequest()]
            );

            try {
                $model->save();

                $this->messageManager->addSuccess(__('You saved this Category.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Category.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    protected function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
