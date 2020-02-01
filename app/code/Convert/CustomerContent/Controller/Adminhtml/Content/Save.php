<?php

namespace Convert\CustomerContent\Controller\Adminhtml\Content;

use Magento\Backend\App\Action;

class Save extends Action
{
    const ADMIN_RESOURCE = 'Convert_CustomerContent::menu_top_content';

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;

    /**
     * @var \Convert\CustomerContent\Model\ContentFactory
     */
    protected $contentFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Convert\CustomerContent\Model\ContentFactory $contentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Backend\Helper\Js $jsHelper,
        \Convert\CustomerContent\Model\ContentFactory $contentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->jsHelper = $jsHelper;
        $this->cacheTypeList = $cacheTypeList;
        $this->contentFactory = $contentFactory;
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
            /** @var \Convert\CustomerContent\Model\Content $model */
            $model = $this->contentFactory->create();
            $id = $this->getRequest()->getParam('content_id');
            if ($id) {
                $model->load($id);
            }
            $file = $this->_filterFoodData($data);
            $model->setData($file);

            $this->_eventManager->dispatch(
                'ccc_content_prepare_save',
                ['content' => $model, 'request' => $this->getRequest()]
            );

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this Content.'));
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
                $this->messageManager->addException($e, __('Something went wrong while saving the Content.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    protected function _filterFoodData(array $rawData)
    {
        //Replace icon with fileuploader field name
        $data = $rawData;
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
        $data['store_id'] = $ids ? $ids : $storeId;
        $txtGenerate = $this->generateRandomString();
        $data['url'] = preg_replace('/[^a-zA-Z0-9_ -%][().][\/]/s', '', $data['title']) . $txtGenerate;
        if (isset($data['file_path'][0]['name']) && $data['file_path'][0]['name']) {
            $data['file_path'] = 'customercontent/tmp/file/' . $data['file_path'][0]['name'];
        } else {
            $data['file_path'] = null;
        }
        if (isset($data['thumbnail'][0]['name']) && $data['thumbnail'][0]['name']) {
            $data['thumbnail'] = 'customercontent/tmp/thumbnail/' . $data['thumbnail'][0]['name'];
        } else {
            $data['thumbnail'] = null;
        }
        $data['file_path'] = str_replace('customercontent/tmp/file/customercontent/tmp/file/', 'customercontent/tmp/file/', $data['file_path']);
        $data['thumbnail'] = str_replace('customercontent/tmp/thumbnail/customercontent/tmp/thumbnail/', 'customercontent/tmp/file/', $data['thumbnail']);
        return $data;
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
