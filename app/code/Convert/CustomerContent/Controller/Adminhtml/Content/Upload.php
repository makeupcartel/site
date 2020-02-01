<?php

namespace Convert\CustomerContent\Controller\Adminhtml\Content;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class Upload extends Action
{
    /**
     * @var \Convert\CustomerContent\Model\Uploader
     */
    protected $uploader;

    /**
     * Upload constructor.
     * @param Action\Context $context
     * @param \Convert\CustomerContent\Model\Uploader $uploader
     */
    public function __construct(
        Action\Context $context,
        \Convert\CustomerContent\Model\Uploader $uploader
    ) {
        parent::__construct($context);
        $this->uploader = $uploader;
    }

    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Convert_CustomerContent::menu_top_content');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $image = $this->getRequest()->getFiles();
        try {
            $result = $this->uploader->saveFileToTmpDir($image['file_path']);
            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
