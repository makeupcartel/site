<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Location;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Amasty\Storelocator\Model\Location');
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                if (isset($data['stores']) && !$data['stores']) {
                    $data['stores'] = ',1,';
                }
                if (isset($data['stores']) && is_array($data['stores'])) {
                    $data['stores'] = ',' . implode(',', $data['stores']) . ',';
                }

                if (isset($data['state_id'])) {
                    $data['state'] = $data['state_id'];
                }

                unset($data['rule']);

                $model->addData($data);
                $model->loadPost($data); // rules

                $data['actions_serialize'] = $this->serializer->serialize($model->getActions()->asArray());

                $this->_prepareForSave($model);

                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccess(__('You saved the item.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('*/*/new');
                }
                return;
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                    $errorMessage = 'Disallowed file type.';
                } else {
                    $errorMessage = 'Something went wrong while saving the item data. Please review the error log.';
                }
                $this->messageManager->addError(
                    __($errorMessage)
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    protected function _prepareForSave($model)
    {
        //upload images
        $data = $this->getRequest()->getPost();
        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'amasty/amlocator/'
        );

        $imagesTypes = array('store', 'marker');
        foreach ($imagesTypes as $type) {
            $field = $type . '_img';

            $files = $this->getRequest()->getFiles();

            $isRemove = array_key_exists('remove_' . $field, $data);
            $fileName = $this->getRequest()->getFiles($field)['name'];
            $hasNew   = !empty($fileName);

            try {
                // remove the old file
                if ($isRemove || $hasNew) {
                    $oldName = isset($data['old_' . $field]) ? $data['old_' . $field] : '';
                    if ($oldName) {
                        $this->_ioFile->rm($path . $oldName);
                        $model->setData($field, '');
                    }
                }

                // upload a new if any
                if (!$isRemove && $hasNew) {
                    //find the first available name
                    $locationId = $model->getId();
                    $newName = $locationId . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $files[$field]['name']);
                    if (substr($newName, 0, 1) == '.') {
                        $newName = 'label' . $newName;
                    }
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => $field]);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->save($path, $newName);

                    $model->setData($field, $newName);
                }
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                    $this->_logger->critical($e);
                }
            }
        }

        return true;
    }
}
