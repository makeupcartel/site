<?php

namespace Unirgy\RapidFlow\Controller\Adminhtml\Profile;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Helper\Data as HelperData;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Uploader;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Unirgy\RapidFlow\Model\Profile;
use Unirgy\RapidFlow\Model\ResourceModel\Profile as ProfileResource;
use Zend\Json\Json;

class Upload extends AbstractProfile
{
    /**
     * @var DirectoryList
     */
    protected $_directoryList;

    /**
     * @var Write
     */
    protected $_directoryWrite;

    public function __construct(Context $context,
                                Profile $profile,
                                HelperData $catalogHelper,
                                ProfileResource $resource,
                                DirectoryList $directoryList,
                                WriteFactory $writeFactory
    )
    {
        $this->_directoryList = $directoryList;
        $this->_directoryWrite = $writeFactory;

        parent::__construct($context, $profile, $catalogHelper, $resource);
    }

    public function execute()
    {
        try {
            $uploader = new Uploader('file');
            $uploader->setAllowedExtensions(['csv', 'txt', '*']);
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);

            $target = $this->_directoryList->getPath('var') . '/urapidflow/import';
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                /** @var \Unirgy\RapidFlow\Model\Profile $model */
                $model = $this->_profile->load($id);
                if ($model && $model->getFileBaseDir()) {
                    $target = $model->getFileBaseDir();
                }
            }
            $this->_directoryWrite->create($target)->create();
            $result = $uploader->save($target);

            $result['cookie'] = [
                'name' => session_name(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain()
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        $this->getResponse()->representJson(Json::encode($result));
    }
}
