<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\Image;

use Aheadworks\Blog\Model\Image\Uploader;
use Magento\Framework\Exception\FileSystemException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Aheadworks\Blog\Model\Image\Info;
use Magento\MediaStorage\Model\File\Uploader as MediaStorageUploader;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class UploaderTest
 * @package Aheadworks\Blog\Test\Unit\Model\Rule\Image
 */
class UploaderTest extends TestCase
{
    /**
     * @var Uploader
     */
    private $model;

    /**
     * @var UploaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uploaderFactoryMock;

    /**
     * @var Info|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageInfoMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->uploaderFactoryMock = $this->createMock(UploaderFactory::class);
        $this->imageInfoMock = $this->createMock(Info::class);
        $this->model = $objectManager->getObject(
            Uploader::class,
            [
                'uploaderFactory' => $this->uploaderFactoryMock,
                'imageInfo' => $this->imageInfoMock
            ]
        );
    }

    /**
     * Test uploadToMediaFolder method
     */
    public function testUploadToMediaFolder()
    {
        $fileType = 'image';
        $fileName = 'file.jpg';
        $fileSize = '123';
        $fileId = 'img';
        $mediaUrl = 'https://ecommerce.aheadworks.com/pub/media/aw_countdown/labels' . $fileName;
        $mediaDirectory = '/var/www/mysite/pub/media/aw_countdown/labels';
        $imgSizeForCss = ['cssWidth' => 100, 'cssHeight' => 100];
        $expected = array_merge(
            [
                'file' => $fileName,
                'size' => $fileSize,
                'type' => $fileType,
                'url' => $mediaUrl
            ],
            $imgSizeForCss
        );

        $writeMock = $this->createMock(WriteInterface::class);
        $this->imageInfoMock->expects($this->once())
            ->method('getMediaDirectory')
            ->willReturn($writeMock);
        $writeMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with(Info::FILE_DIR)
            ->willReturn($mediaDirectory);

        $uploaderMock = $this->createMock(MediaStorageUploader::class);

        $this->uploaderFactoryMock->expects($this->once())
            ->method('create')
            ->with(['fileId' => $fileId])
            ->willReturn($uploaderMock);
        $uploaderMock->expects($this->once())
            ->method('setAllowRenameFiles')
            ->with(true)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('setFilesDispersion')
            ->with(false)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('setAllowedExtensions')
            ->with($this->model->getAllowedExtensions())
            ->willReturnSelf();

        $uploaderMock->expects($this->any())
            ->method('save')
            ->with($mediaDirectory)
            ->willReturn([
                'file' => $fileName,
                'size' => $fileSize,
                'type' => $fileType
            ]);
        $this->imageInfoMock->expects($this->any())
            ->method('getMediaUrl')
            ->with($fileName)
            ->willReturn($mediaUrl);
        $this->imageInfoMock->expects($this->any())
            ->method('getImgSizeForCss')
            ->with($fileName)
            ->willReturn($imgSizeForCss);

        $this->assertEquals($expected, $this->model->uploadToMediaFolder($fileId));
    }

    /**
     * Test getStat method
     *
     * @expectedException \Magento\Framework\Exception\FileSystemException
     */
    public function testUploadToMediaFolderOnException()
    {
        $fileId = 'img';
        $exception = new FileSystemException(__('Exception message.'));

        $this->imageInfoMock->expects($this->once())
            ->method('getMediaDirectory')
            ->willThrowException($exception);

        $this->model->uploadToMediaFolder($fileId);
    }

    /**
     * Testing of getAllowedExtensions method
     */
    public function testGetAllowedExtensions()
    {
        $allowedExtensions = ['jpg', 'jpeg', 'gif', 'png'];

        $this->assertEquals($allowedExtensions, $this->model->getAllowedExtensions());
    }
}
