<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Post;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class FeaturedImageInfo
 * @package Aheadworks\Blog\Model\Post
 */
class FeaturedImageInfo
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
    }

    /**
     * Get image file name
     *
     * @param string $imageFilePath
     * @return bool|string
     */
    public function getImageFileName($imageFilePath)
    {
        $mediaDirectory = $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath();

        $fullImageFilePath =  $mediaDirectory . $imageFilePath;

        if (file_exists($fullImageFilePath)) {
            return basename($mediaDirectory . $imageFilePath);
        }
        return false;
    }

    /**
     * Get url to media folder
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            );
    }

    /**
     * Get absolute url to image
     *
     * @param $imageFilePath
     * @return bool|string
     */
    public function getImageUrl($imageFilePath)
    {
        if ($imageFilePath) {
            return $this->getMediaUrl() . $imageFilePath;
        }
        return '';
    }
}
