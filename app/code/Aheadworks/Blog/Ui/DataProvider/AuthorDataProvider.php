<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Blog\Ui\DataProvider;

use Aheadworks\Blog\Api\Data\AuthorInterface;
use Aheadworks\Blog\Model\ResourceModel\Author\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Aheadworks\Blog\Model\Image\Info as ImageInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class AuthorDataProvider
 * @package Aheadworks\Blog\Ui\DataProvider
 */
class AuthorDataProvider extends AbstractDataProvider
{
    /**
     * Data persistor key
     */
    const DATA_PERSISTOR_KEY = 'aw_blog_author_form';
    
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ImageInfo
     */
    private $imageInfo;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param DataPersistorInterface $dataPersistor
     * @param ImageInfo $imageInfo
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        DataPersistorInterface $dataPersistor,
        ImageInfo $imageInfo,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->dataPersistor = $dataPersistor;
        $this->imageInfo = $imageInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];
        $dataFromForm = $this->dataPersistor->get(self::DATA_PERSISTOR_KEY);
        $id = $this->request->getParam($this->getRequestFieldName());
        if (!empty($dataFromForm)) {
            $data = $dataFromForm;
            $this->dataPersistor->clear(self::DATA_PERSISTOR_KEY);
        } else {
            /** @var \Aheadworks\Blog\Model\Author $author */
            foreach ($this->getCollection()->getItems() as $author) {
                if ($id == $author->getId()) {
                    $data = $author->getData();
                }
            }
        }
        $preparedData[$id] = $data ? $this->prepareFormData($data) : null;

        return $preparedData;
    }

    /**
     * Prepare form data
     *
     * @param array $itemData
     * @return array
     * @throws LocalizedException
     */
    private function prepareFormData($itemData)
    {
        $itemData = $this->prepareImageData($itemData);

        return $itemData;
    }

    /**
     * Prepare featured image data
     *
     * @param array $itemData
     * @return array
     * @throws LocalizedException
     */
    private function prepareImageData(array $itemData)
    {
        if (!empty($itemData[AuthorInterface::IMAGE_FILE])) {
            $fileName = $itemData[AuthorInterface::IMAGE_FILE];
            $itemData[AuthorInterface::IMAGE_FILE] = [
                0 => [
                    'file' => $fileName,
                    'url' => $this->imageInfo->getMediaUrl($fileName),
                    'size' => $this->imageInfo->getStat($fileName)['size'],
                    'type' => 'image'
                ]
            ];
        }
        return $itemData;
    }
}
