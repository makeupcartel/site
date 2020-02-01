<?php

namespace Convert\CategoryThumbnail\Controller\Adminhtml\Image;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Upload
 *
 * @package Convert\CategoryThumbnail\Controller\Adminhtml\Image
 */
class Upload extends \Magento\Backend\App\Action
{
	/**
	 * @var \Magento\Catalog\Model\ImageUploader
	 */
	protected $imageUploader;

	/**
	 * Upload constructor.
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Magento\Catalog\Model\ImageUploader $imageUploader
	 */
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Catalog\Model\ImageUploader $imageUploader
	)
	{
		parent::__construct($context);
		$this->imageUploader = $imageUploader;
	}

	/**
	 * Check admin permissions for this controller
	 *
	 * @return boolean
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magento_Catalog::categories');
	}

	/**
	 * Upload file controller action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute()
	{
		$imageId = $this->_request->getParam('param_name', 'image');

		try {
			$result = $this->imageUploader->saveFileToTmpDir($imageId);

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
