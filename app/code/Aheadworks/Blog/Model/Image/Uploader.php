<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Image;

use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * Class Uploader
 * @package Aheadworks\Blog\Model\Image
 */
class Uploader
{
    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var Info
     */
    private $imageInfo;

    /**
     * @param UploaderFactory $uploaderFactory
     * @param Info $imageInfo
     */
    public function __construct(
        UploaderFactory $uploaderFactory,
        Info $imageInfo
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->imageInfo = $imageInfo;
    }

    /**
     * Upload image to media directory
     *
     * @param string $fileId
     * @return array
     * @throws \Exception
     */
    public function uploadToMediaFolder($fileId)
    {
        $result = ['file' => '', 'size' => '', 'type' => '', 'cssWidth' => '', 'cssHeight' => ''];
        $mediaDirectory = $this->imageInfo
            ->getMediaDirectory()
            ->getAbsolutePath(Info::FILE_DIR);

        /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader
            ->setAllowRenameFiles(true)
            ->setFilesDispersion(false)
            ->setAllowedExtensions($this->getAllowedExtensions());

        $result = array_intersect_key($uploader->save($mediaDirectory), $result);
        $result = array_merge($this->imageInfo->getImgSizeForCss($result['file']), $result);
        $result['url'] = $this->imageInfo->getMediaUrl($result['file']);

        return $result;
    }

    /**
     * Get allowed file extensions
     *
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return ['jpg', 'jpeg', 'gif', 'png'];
    }
}
