<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Aheadworks\Blog\Model\Image\Uploader;
use Magento\Framework\Controller\Result\Json;

/**
 * Class Upload
 * @package Aheadworks\Blog\Controller\Adminhtml
 */
abstract class Upload extends Action
{
    /**
     * @var string
     */
    const FILE_ID = '';

    /**
     * @var Uploader
     */
    private $imageUploader;

    /**
     * @param Context $context
     * @param Uploader $imageUploader
     */
    public function __construct(
        Context $context,
        Uploader $imageUploader
    ) {
        parent::__construct($context);
        $this->imageUploader = $imageUploader;
    }

    /**
     * Image upload action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $result = $this->imageUploader->uploadToMediaFolder(static::FILE_ID);
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $resultJson->setData($result);
    }
}
