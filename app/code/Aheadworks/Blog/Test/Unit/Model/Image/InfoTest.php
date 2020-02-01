<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Test\Unit\Model\Image;

use Aheadworks\Blog\Model\Image\Info;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Image;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Image\Factory as ImageFactory;

/**
 * Class InfoTest
 * @package Aheadworks\Blog\Test\Unit\Model\Image
 */
class InfoTest extends TestCase
{
    /**
     * @var Info
     */
    private $model;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var ImageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $imageProcessorFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->imageProcessorFactoryMock = $this->createMock(ImageFactory::class);
        $this->model = $objectManager->getObject(
            Info::class,
            [
                'storeManager' => $this->storeManagerMock,
                'filesystem' => $this->filesystemMock,
                'imageProcessorFactory' => $this->imageProcessorFactoryMock
            ]
        );
    }

    /**
     * Test getMediaUrl method
     */
    public function testGetMediaUrl()
    {
        $fileName = '1.png';
        $storeBaseUrl = 'url';
        $expected = $storeBaseUrl . Info::FILE_DIR . '/' . $fileName;

        $storeMock = $this->createMock(Store::class);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($storeBaseUrl);

        $this->assertEquals($expected, $this->model->getMediaUrl($fileName));
    }

    /**
     * Test getMediaUrl method
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetMediaUrlOnException()
    {
        $fileName = '1.png';
        $exception = new NoSuchEntityException(__('Exception message.'));

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willThrowException($exception);

        $this->model->getMediaUrl($fileName);
    }

    /**
     * Test getStat method
     */
    public function testGetStat()
    {
        $expected = ['param1' => 'val1', 'param2' => 'val2'];
        $fileName = '1.png';
        $filePath = Info::FILE_DIR . '/' . ltrim($fileName, '/');

        $writeMock = $this->createMock(WriteInterface::class);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($writeMock);

        $writeMock->expects($this->once())
            ->method('stat')
            ->with($filePath)
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getStat($fileName));
    }

    /**
     * Test getImgSizeForCss method
     *
     * @param int $imgHeight
     * @param int $imgWidth
     * @param float $expImgHeight
     * @param float $expImgWidth
     * @dataProvider getImgSizeForCssDataProvider
     */
    public function testGetImgSizeForCss($imgHeight, $imgWidth, $expImgHeight, $expImgWidth)
    {
        $expected = ['cssWidth' => $expImgHeight, 'cssHeight' => $expImgWidth];
        $fileName = '1.png';
        $filePath = Info::FILE_DIR . '/' . ltrim($fileName, '/');
        $absoluteFilePath = 'absolute/' . $filePath;

        $writeMock = $this->createMock(WriteInterface::class);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($writeMock);

        $writeMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with($filePath)
            ->willReturn($absoluteFilePath);

        $imageMock = $this->createMock(Image::class);
        $this->imageProcessorFactoryMock->expects($this->once())
            ->method('create')
            ->with($absoluteFilePath)
            ->willReturn($imageMock);

        $imageMock->expects($this->once())
            ->method('getOriginalHeight')
            ->willReturn($imgHeight);
        $imageMock->expects($this->once())
            ->method('getOriginalWidth')
            ->willReturn($imgWidth);

        $this->assertEquals($expected, $this->model->getImgSizeForCss($fileName));
    }

    /**
     * Data provider for getImgSizeForCss test
     *
     * @return array
     */
    public function getImgSizeForCssDataProvider()
    {
        return [
            [100, 100, 100, 100],
            [200, 100, 50.0, 100.0],
            [100, 200, 100.0, 50.0],
            [763, 1200, 100.0, 64.0],
            [1523, 550, 36.0, 100.0]
        ];
    }

    /**
     * Test getStat method
     */
    public function testGetMediaDirectory()
    {
        $writeMock = $this->createMock(WriteInterface::class);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($writeMock);

        $this->assertSame($writeMock, $this->model->getMediaDirectory());
        $this->assertSame($writeMock, $this->model->getMediaDirectory());
    }

    /**
     * Test getStat method
     *
     * @expectedException \Magento\Framework\Exception\FileSystemException
     */
    public function testGetMediaDirectoryOnException()
    {
        $exception = new FileSystemException(__('Exception message.'));

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->willThrowException($exception);

        $this->model->getMediaDirectory();
    }
}
