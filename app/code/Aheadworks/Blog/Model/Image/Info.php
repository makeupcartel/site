<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Model\Image;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Image\Factory as ImageFactory;

/**
 * Class Info
 * @package Aheadworks\Blog\Model\Image
 */
class Info
{
    /**
     * @var string
     */
    const FILE_DIR = 'aw_blog';

    /**
     * @var string
     */
    const BASE_CSS_WIDTH = 100;

    /**
     * @var string
     */
    const BASE_CSS_HEIGHT = 100;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var ImageFactory
     */
    private $imageProcessorFactory;

    /**
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param ImageFactory $imageProcessorFactory
     */
    public function __construct(
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        ImageFactory $imageProcessorFactory
    ) {
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->imageProcessorFactory = $imageProcessorFactory;
    }
    /**
     * Get file url
     *
     * @param string $file
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl($file)
    {
        $file = ltrim(str_replace('\\', '/', $file), '/');
        $storeBaseUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        return $storeBaseUrl . self::FILE_DIR . '/' . $file;
    }

    /**
     * Get file statistics data
     *
     * @param string $fileName
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getStat($fileName)
    {
        return $this->getMediaDirectory()->stat($this->getFilePath($fileName));
    }

    /**
     * Get file statistics data
     *
     * @param string $fileName
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getImgSizeForCss($fileName)
    {
        $filePath = $this->getMediaDirectory()->getAbsolutePath($this->getFilePath($fileName));
        $imageProcessor = $this->imageProcessorFactory->create($filePath);
        $imgHeight = $imageProcessor->getOriginalHeight();
        $imgWidth = $imageProcessor->getOriginalWidth();

        if ($imgWidth > self::BASE_CSS_WIDTH) {
            $imgHeight = $imgHeight * (self::BASE_CSS_WIDTH / $imgWidth);
            $imgWidth = self::BASE_CSS_WIDTH;
        }
        if ($imgHeight > self::BASE_CSS_HEIGHT) {
            $imgWidth = $imgWidth * (self::BASE_CSS_HEIGHT / $imgHeight);
            $imgHeight = self::BASE_CSS_HEIGHT;
        }

        return ['cssWidth' => round($imgWidth), 'cssHeight' => round($imgHeight)];
    }

    /**
     * Get WriteInterface instance
     *
     * @return WriteInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getMediaDirectory()
    {
        if ($this->mediaDirectory === null) {
            $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        }
        return $this->mediaDirectory;
    }

    /**
     * Get file path
     *
     * @param string $fileName
     * @return string
     */
    private function getFilePath($fileName)
    {
        return self::FILE_DIR . '/' . ltrim($fileName, '/');
    }
}
